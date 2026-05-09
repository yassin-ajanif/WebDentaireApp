<?php

namespace App\Entities\Chronology\Ui\Livewire;

use App\Entities\Chronology\Contracts\ChronologyServiceInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
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

    public function render()
    {
        $date = Carbon::parse($this->selectedDate);

        $sessionRows = $this->chronology()->getSessionsForDay($date)
            ->map(function ($row) {
                $startedAt = Carbon::parse($row->started_at);
                $completedAt = Carbon::parse($row->completed_at);

                $name = trim($row->first_name . ' ' . $row->last_name);
                if ($name === '') {
                    $name = '#' . $row->patient_id;
                }

                return [
                    'patient_id' => $row->patient_id,
                    'treatment_info_id' => (int) $row->latest_treatment_info_id,
                    'off' => $name,
                    'started_at_raw' => $startedAt,
                    'completed_at_raw' => $completedAt,
                    'started_at' => $startedAt->format('H:i'),
                    'completed_at' => $completedAt->format('H:i'),
                    'received' => number_format((float) ($row->received_total ?? 0), 2, '.', ''),
                ];
            })
            ->values();

        $gapAlerts = collect();
        for ($i = 1; $i < $sessionRows->count(); $i++) {
            $previous = $sessionRows[$i - 1];
            $current = $sessionRows[$i];
            $gapMinutes = $previous['completed_at_raw']->diffInMinutes($current['started_at_raw'], false);

            if ($gapMinutes > 30) {
                $gapAlerts->push([
                    'minutes' => $gapMinutes,
                    'from' => $previous['completed_at'],
                    'to' => $current['started_at'],
                ]);
            }
        }

        $cancelledTreatments = $this->treatments()->listCancellationsForDate($date);
        $cancelledSessions = $this->chronology()->getCancelledSessionsForDay($date);

        $totalReceived = $sessionRows->sum(fn (array $row) => (float) $row['received']);
        $totalCancelledTreatments = $cancelledTreatments->sum(fn ($c) => $c['refund_amount'] ?? (float) $c->refund_amount);
        $totalCancelledSessions = $cancelledSessions->sum(fn ($s) => (float) $s->refund_amount);
        $netTotal = $totalReceived - $totalCancelledTreatments - $totalCancelledSessions;

        return view('chronology::appointment-timeline-page', [
            'selectedDateLabel' => $date->translatedFormat('l d/m/Y'),
            'sessionRows' => $sessionRows,
            'gapAlerts' => $gapAlerts,
            'cancelledTreatments' => $cancelledTreatments,
            'cancelledSessions' => $cancelledSessions,
            'totalReceived' => number_format($totalReceived, 2, '.', ''),
            'totalCancelledTreatments' => number_format($totalCancelledTreatments, 2, '.', ''),
            'totalCancelledSessions' => number_format($totalCancelledSessions, 2, '.', ''),
            'netTotal' => number_format($netTotal, 2, '.', ''),
            'isToday' => $date->isToday(),
        ])->title(__('Chronologie des rendez-vous'));
    }

    private function chronology(): ChronologyServiceInterface
    {
        return app(ChronologyServiceInterface::class);
    }

    private function treatments(): TreatmentInfoServiceInterface
    {
        return app(TreatmentInfoServiceInterface::class);
    }
}
