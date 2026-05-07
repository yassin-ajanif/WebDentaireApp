<?php

namespace App\Entities\Setting\Ui\Livewire;

use App\Entities\Setting\Contracts\QueueSettingsServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QueueSettingsPage extends Component
{
    public int $average_consultation_minutes = 20;

    public function mount(): void
    {
        $this->average_consultation_minutes = $this->settings()->getQueuePredictionConfig()['average_consultation_minutes'];
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

    public function render()
    {
        return view('setting::queue-settings-page')->title(__('Queue settings'));
    }

    private function settings(): QueueSettingsServiceInterface
    {
        return app(QueueSettingsServiceInterface::class);
    }
}
