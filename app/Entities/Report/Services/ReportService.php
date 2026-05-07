<?php

namespace App\Entities\Report\Services;

use App\Entities\Report\Contracts\ReportServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService implements ReportServiceInterface
{
    public function revenueByPeriod(Carbon $from, Carbon $to): Collection
    {
        return DB::table('treatment_sessions')
            ->selectRaw('DATE(created_at) as paid_date, SUM(received_payment) as received_total')
            ->whereBetween('created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('paid_date')
            ->get()
            ->map(function ($row): array {
                return [
                    'date' => Carbon::parse($row->paid_date),
                    'received_total' => (float) $row->received_total,
                ];
            });
    }

    public function patientCredits(): Collection
    {
        return DB::table('treatment_infos as ti')
            ->join('patients as p', 'p.id', '=', 'ti.patient_id')
            ->selectRaw('
                p.id as patient_id,
                p.first_name,
                p.last_name,
                p.telephone,
                SUM(ti.global_price) as total_plan,
                SUM(ti.remaining_amount) as total_credit
            ')
            ->where('ti.remaining_amount', '>', 0)
            ->groupBy('p.id', 'p.first_name', 'p.last_name', 'p.telephone')
            ->orderByDesc('total_credit')
            ->get()
            ->map(function ($row): array {
                $totalPlan = (float) $row->total_plan;
                $totalCredit = (float) $row->total_credit;

                return [
                    'patient_id' => (int) $row->patient_id,
                    'name' => trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: '—',
                    'telephone' => $row->telephone ?? '—',
                    'total_plan' => $totalPlan,
                    'paid' => $totalPlan - $totalCredit,
                    'credit' => $totalCredit,
                ];
            });
    }
}
