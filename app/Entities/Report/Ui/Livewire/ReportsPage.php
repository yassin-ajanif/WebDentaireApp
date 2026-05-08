<?php

namespace App\Entities\Report\Ui\Livewire;

use App\Entities\Report\Contracts\ReportServiceInterface;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReportsPage extends Component
{
    public string $fromDate = '';

    public string $toDate = '';

    public function mount(): void
    {
        $today = Carbon::today()->toDateString();
        $this->fromDate = $today;
        $this->toDate = $today;
    }

    public function setRange(string $range): void
    {
        if (! in_array($range, ['today', 'last7', 'month'], true)) {
            return;
        }

        $today = Carbon::today();

        if ($range === 'today') {
            $this->fromDate = $today->toDateString();
            $this->toDate = $today->toDateString();
        } elseif ($range === 'last7') {
            $this->fromDate = $today->copy()->subDays(6)->toDateString();
            $this->toDate = $today->toDateString();
        } else {
            $this->fromDate = $today->copy()->startOfMonth()->toDateString();
            $this->toDate = $today->copy()->endOfMonth()->toDateString();
        }
    }

    /** @return ''|'today'|'last7'|'month' */
    private function activePreset(): string
    {
        $today = Carbon::today();

        if ($this->fromDate === $today->toDateString() && $this->toDate === $today->toDateString()) {
            return 'today';
        }

        $last7From = $today->copy()->subDays(6)->toDateString();
        if ($this->fromDate === $last7From && $this->toDate === $today->toDateString()) {
            return 'last7';
        }

        if ($this->fromDate === $today->copy()->startOfMonth()->toDateString()
            && $this->toDate === $today->copy()->endOfMonth()->toDateString()) {
            return 'month';
        }

        return '';
    }

    private function reports(): ReportServiceInterface
    {
        return app(ReportServiceInterface::class);
    }

    public function render()
    {
        $from = Carbon::parse($this->fromDate)->startOfDay();
        $toDay = Carbon::parse($this->toDate)->startOfDay();

        if ($from->gt($toDay)) {
            [$from, $toDay] = [$toDay->copy(), $from->copy()];
        }

        $toEnd = $toDay->copy()->endOfDay();

        $rangeLabel = $from->isSameDay($toDay)
            ? $from->translatedFormat('d/m/Y')
            : $from->translatedFormat('d/m/Y').' - '.$toDay->translatedFormat('d/m/Y');

        $revenueRows = $this->reports()->revenueByPeriod($from, $toEnd)
            ->map(function (array $row): array {
                return [
                    'date' => $row['date']->format('Y-m-d'),
                    'label' => $row['date']->format('d/m/Y'),
                    'received_total' => number_format($row['received_total'], 2, '.', ''),
                ];
            });

        $credits = $this->reports()->patientCredits()
            ->map(function (array $row): array {
                return [
                    ...$row,
                    'total_plan' => number_format($row['total_plan'], 2, '.', ''),
                    'paid' => number_format($row['paid'], 2, '.', ''),
                    'credit' => number_format($row['credit'], 2, '.', ''),
                ];
            });

        $corrections = $this->reports()->treatmentCorrectionsByPeriod($from, $toEnd)
            ->map(function (array $row): array {
                return [
                    ...$row,
                    'old_global_price' => number_format($row['old_global_price'], 2, '.', ''),
                    'new_global_price' => number_format($row['new_global_price'], 2, '.', ''),
                ];
            });

        $sessionCorrections = $this->reports()->sessionCorrectionsByPeriod($from, $toEnd)
            ->map(function (array $row): array {
                return [
                    ...$row,
                    'old_received_payment' => number_format($row['old_received_payment'], 2, '.', ''),
                    'new_received_payment' => number_format($row['new_received_payment'], 2, '.', ''),
                ];
            });

        $cancelledTreatments = $this->reports()->cancelledTreatmentsByPeriod($from, $toEnd)
            ->map(function (array $row): array {
                return [
                    ...$row,
                    'global_price' => number_format($row['global_price'], 2, '.', ''),
                    'refund_amount' => number_format($row['refund_amount'], 2, '.', ''),
                ];
            });

        $cancelledSessions = $this->reports()->cancelledSessionsByPeriod($from, $toEnd)
            ->map(function (array $row): array {
                return [
                    ...$row,
                    'received_payment' => number_format($row['received_payment'], 2, '.', ''),
                ];
            });

        return view('report::reports-page', [
            'activePreset' => $this->activePreset(),
            'rangeLabel' => $rangeLabel,
            'revenueRows' => $revenueRows,
            'totalRevenue' => number_format($revenueRows->sum(fn (array $row) => (float) $row['received_total']), 2, '.', ''),
            'credits' => $credits,
            'totalCredits' => number_format($credits->sum(fn (array $row) => (float) $row['credit']), 2, '.', ''),
            'corrections' => $corrections,
            'sessionCorrections' => $sessionCorrections,
            'cancelledTreatments' => $cancelledTreatments,
            'totalCancelledTreatments' => number_format($cancelledTreatments->sum(fn (array $row) => (float) $row['refund_amount']), 2, '.', ''),
            'cancelledSessions' => $cancelledSessions,
            'totalCancelledSessions' => number_format($cancelledSessions->sum(fn (array $row) => (float) $row['received_payment']), 2, '.', ''),
        ])->title(__('Reports'));
    }
}
