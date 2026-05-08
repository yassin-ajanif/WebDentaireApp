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

    public function treatmentCorrectionsByPeriod(Carbon $from, Carbon $to): Collection
    {
        return DB::table('treatment_corrections as tc')
            ->join('treatment_infos as ti', 'ti.id', '=', 'tc.treatment_info_id')
            ->join('patients as p', 'p.id', '=', 'ti.patient_id')
            ->selectRaw('
                tc.id,
                tc.treatment_info_id,
                tc.old_global_price,
                tc.new_global_price,
                tc.old_description,
                tc.new_description,
                tc.reason,
                tc.created_at,
                p.id as patient_id,
                p.first_name,
                p.last_name
            ')
            ->whereBetween('tc.created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ])
            ->orderByDesc('tc.created_at')
            ->orderByDesc('tc.id')
            ->get()
            ->map(function ($row): array {
                $createdAt = Carbon::parse($row->created_at);

                return [
                    'id' => (int) $row->id,
                    'patient_id' => (int) $row->patient_id,
                    'treatment_info_id' => (int) $row->treatment_info_id,
                    'patient_name' => trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: '—',
                    'created_at' => $createdAt,
                    'created_label' => $createdAt->format('d/m/Y H:i'),
                    'old_global_price' => (float) $row->old_global_price,
                    'new_global_price' => (float) $row->new_global_price,
                    'old_description' => (string) $row->old_description,
                    'new_description' => (string) $row->new_description,
                    'reason' => (string) $row->reason,
                ];
            });
    }

    public function sessionCorrectionsByPeriod(Carbon $from, Carbon $to): Collection
    {
        return DB::table('treatment_session_corrections as sc')
            ->join('treatment_infos as ti', 'ti.id', '=', 'sc.treatment_info_id')
            ->join('patients as p', 'p.id', '=', 'ti.patient_id')
            ->leftJoin('users as u', 'u.id', '=', 'sc.created_by')
            ->selectRaw('
                sc.id,
                sc.treatment_session_id,
                sc.treatment_info_id,
                sc.old_session_date,
                sc.new_session_date,
                sc.old_received_payment,
                sc.new_received_payment,
                sc.old_notes,
                sc.new_notes,
                sc.reason,
                sc.created_at,
                p.id as patient_id,
                p.first_name,
                p.last_name,
                ti.description as treatment_description,
                u.name as edited_by
            ')
            ->whereBetween('sc.created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ])
            ->orderByDesc('sc.created_at')
            ->orderByDesc('sc.id')
            ->get()
            ->map(function ($row): array {
                $createdAt = Carbon::parse($row->created_at);
                $newSessionDate = Carbon::parse($row->new_session_date);

                return [
                    'id' => (int) $row->id,
                    'patient_id' => (int) $row->patient_id,
                    'treatment_info_id' => (int) $row->treatment_info_id,
                    'patient_name' => trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: '—',
                    'treatment_description' => (string) ($row->treatment_description ?? '—'),
                    'created_at' => $createdAt,
                    'created_label' => $createdAt->format('d/m/Y H:i'),
                    'session_label' => $newSessionDate->format('d/m/Y H:i'),
                    'old_received_payment' => (float) $row->old_received_payment,
                    'new_received_payment' => (float) $row->new_received_payment,
                    'old_notes' => $row->old_notes,
                    'new_notes' => $row->new_notes,
                    'reason' => (string) $row->reason,
                    'edited_by' => (string) ($row->edited_by ?? '—'),
                ];
            });
    }
}
