<?php

namespace App\Entities\Setting\Ui\Livewire;

use App\Entities\Setting\Contracts\QueueSettingsServiceInterface;
use App\Entities\Setting\Models\Setting;
use App\Services\BackupService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QueueSettingsPage extends Component
{
    public int $average_consultation_minutes = 20;

    public string $backupPath = '';
    public string $pgBinDir = '';

    public bool $autoBackupEnabled = false;
    public int $autoInterval = 30;
    public int $autoRetentionDays = 30;

    public string $backupMessage = '';
    public bool $backupSuccess = false;

    public function mount(): void
    {
        $this->average_consultation_minutes = $this->settings()->getQueuePredictionConfig()['average_consultation_minutes'];
        $this->backupPath = storage_path('app' . DIRECTORY_SEPARATOR . 'backups');
        $this->pgBinDir = env('PG_DUMP_PATH', '');

        $config = Setting::query()->where('key', 'backup_auto')->first();
        if ($config) {
            $this->autoBackupEnabled = $config->value['enabled'] ?? false;
            $this->autoInterval = $config->value['interval_seconds'] ?? 30;
            $this->autoRetentionDays = $config->value['retention_days'] ?? 30;
            $this->backupPath = $config->value['storage_path'] ?? $this->backupPath;
            $this->pgBinDir = $config->value['pg_bin_dir'] ?? $this->pgBinDir;
        }
    }

    public function save(): void
    {
        $this->validate([
            'average_consultation_minutes' => ['required', 'integer', 'min:1', 'max:480'],
        ]);

        $this->settings()->updateQueuePredictionConfig([
            'average_consultation_minutes' => $this->average_consultation_minutes,
        ]);

        $this->backupMessage = __('Settings saved.');
        $this->backupSuccess = true;
    }

    public function saveAutoBackup(): void
    {
        $this->validate([
            'autoInterval' => ['required', 'integer', 'min:1'],
            'autoRetentionDays' => ['required', 'integer', 'min:1', 'max:365'],
            'backupPath' => ['required', 'string'],
        ]);

        Setting::query()->updateOrCreate(
            ['key' => 'backup_auto'],
            [
                'value' => [
                    'enabled' => $this->autoBackupEnabled,
                    'interval_seconds' => $this->autoInterval,
                    'retention_days' => $this->autoRetentionDays,
                    'storage_path' => $this->backupPath,
                    'pg_bin_dir' => $this->pgBinDir,
                ],
            ]
        );

        $this->backupMessage = __('Auto backup settings saved.');
        $this->backupSuccess = true;
    }

    public function createBackup(): void
    {
        $this->validate([
            'backupPath' => ['required', 'string'],
        ]);

        try {
            $path = app(BackupService::class)->create($this->backupPath, $this->pgBinDir ?: null);
            $this->backupMessage = __('Backup created') . ': ' . basename($path);
            $this->backupSuccess = true;
        } catch (\RuntimeException $e) {
            $this->backupMessage = $e->getMessage();
            $this->backupSuccess = false;
        }
    }

    public function getIntervalOptionsProperty(): array
    {
        return [
            ['value' => 30, 'label' => __('Every 30 seconds')],
            ['value' => 60, 'label' => __('Every minute')],
            ['value' => 300, 'label' => __('Every 5 minutes')],
            ['value' => 600, 'label' => __('Every 10 minutes')],
            ['value' => 900, 'label' => __('Every 15 minutes')],
            ['value' => 1800, 'label' => __('Every 30 minutes')],
            ['value' => 3600, 'label' => __('Every hour')],
            ['value' => 7200, 'label' => __('Every 2 hours')],
        ];
    }

    public function render()
    {
        return view('setting::queue-settings-page')->title(__('Queue settings'));
    }

    private function settings(): QueueSettingsServiceInterface
    {
        return app(QueueSettingsServiceInterface::class);
    }
}
