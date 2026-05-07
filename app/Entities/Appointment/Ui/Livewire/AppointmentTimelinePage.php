<?php

namespace App\Entities\Appointment\Ui\Livewire;

use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AppointmentTimelinePage extends Component
{
    public string $selectedDate = '';

    public function mount(): void
    {
        $this->selectedDate = Carbon::today()->toDateString();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
    }

    public function today(): void
    {
        $this->selectedDate = Carbon::today()->toDateString();
    }

    private function appointments(): AppointmentServiceInterface
    {
        return app(AppointmentServiceInterface::class);
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate);
        $rows = $this->appointments()->listCompletedTimeline($date)
            ->sortBy('started_at')
            ->values()
            ->map(function ($appointment) {
                $startedAt = $appointment->started_at;
                $completedAt = $appointment->completed_at;
                if ($startedAt === null || $completedAt === null) {
                    return null;
                }

                return [
                    'id' => $appointment->id,
                    'off' => $appointment->queueDisplayName(),
                    'patient_id' => $appointment->patient_id,
                    'started_at_raw' => $startedAt,
                    'completed_at_raw' => $completedAt,
                    'started_at' => $startedAt->format('H:i'),
                    'completed_at' => $completedAt->format('H:i'),
                    'received' => number_format((float) ($appointment->received_total ?? 0), 2, '.', ''),
                ];
            })
            ->filter()
            ->values();

        $gapAlerts = collect();
        for ($i = 1; $i < $rows->count(); $i++) {
            $previous = $rows[$i - 1];
            $current = $rows[$i];
            $gapMinutes = $previous['completed_at_raw']->diffInMinutes($current['started_at_raw'], false);

            if ($gapMinutes > 30) {
                $gapAlerts->push([
                    'minutes' => $gapMinutes,
                    'from' => $previous['completed_at'],
                    'to' => $current['started_at'],
                ]);
            }
        }
        $totalReceived = $rows->sum(fn (array $row) => (float) $row['received']);

        return view('appointment::appointment-timeline-page', [
            'selectedDateLabel' => $date->translatedFormat('l d/m/Y'),
            'rows' => $rows,
            'gapAlerts' => $gapAlerts,
            'totalReceived' => number_format($totalReceived, 2, '.', ''),
            'isToday' => $date->isToday(),
        ])->title(__('Chronologie des rendez-vous'));
    }
}