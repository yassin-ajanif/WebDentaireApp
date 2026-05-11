<?php

namespace App\Entities\Setting\Ui\Livewire;

use App\Entities\Setting\Contracts\QueueSettingsServiceInterface;
use App\Services\BackupService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QueueSettingsPage extends Component
{
    public int $average_consultation_minutes = 20;

    public string $backupPath = '';
    public string $pgBinDir = '';

    public function mount(): void
    {
        $this->average_consultation_minutes = $this->settings()->getQueuePredictionConfig()['average_consultation_minutes'];
        $this->backupPath = storage_path('app' . DIRECTORY_SEPARATOR . 'backups');
        $this->pgBinDir = env('PG_DUMP_PATH', '');
    }

    public function save(): void
    {
        $this->validate([
            'average_consultation_minutes' => ['required', 'integer', 'min:1', 'max:480'],
        ]);

        $this->settings()->updateQueuePredictionConfig([
            'average_consultation_minutes' => $this->average_consultation_minutes,
        ]);

        session()->flash('status', __('Settings saved.'));
    }

    public string $backupMessage = '';
    public bool $backupSuccess = false;

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

    public function render()
    {
        return view('setting::queue-settings-page')->title(__('Queue settings'));
    }

    private function settings(): QueueSettingsServiceInterface
    {
        return app(QueueSettingsServiceInterface::class);
    }
}
