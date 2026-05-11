<?php

namespace App\Services;

use App\Entities\Setting\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PeriodicBackupService
{
    public function run(): void
    {
        $config = Setting::query()->where('key', 'backup_auto')->first();
        if (!$config || empty($config->value['enabled'])) {
            return;
        }

        $interval = $config->value['interval_seconds'] ?? 300;
        $storagePath = $config->value['storage_path'] ?? '';
        $pgBinDir = $config->value['pg_bin_dir'] ?? null;
        $retentionDays = $config->value['retention_days'] ?? 30;

        if (!$storagePath) {
            return;
        }

        $last = Setting::query()->where('key', 'backup_last_time')->first();
        $lastTime = $last ? $last->value['last'] ?? null : null;

        if ($lastTime) {
            $diff = now()->timestamp - (int) $lastTime;
            if ($diff >= 0 && $diff < $interval) {
                return;
            }
        }

        $lock = Cache::lock('backup', 120);
        if (!$lock->get()) {
            return;
        }

        try {
            app(BackupService::class)->create($storagePath, $pgBinDir);

            Setting::query()->updateOrCreate(
                ['key' => 'backup_last_time'],
                ['value' => ['last' => now()->timestamp]],
            );

            $this->cleanup($storagePath, $retentionDays);
        } catch (\Throwable $e) {
            Log::error('Auto backup failed: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }

    private function cleanup(string $path, int $retentionDays): void
    {
        $cutoff = now()->subDays($retentionDays)->timestamp;
        $files = glob(rtrim($path, '\\/') . DIRECTORY_SEPARATOR . 'backup-*.sql');
        if ($files === false) {
            return;
        }
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
