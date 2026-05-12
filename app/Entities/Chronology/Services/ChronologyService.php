<?php

namespace App\Entities\Chronology\Services;

use App\Entities\Chronology\Contracts\ChronologyServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChronologyService implements ChronologyServiceInterface
{
    public function getSessionsForDay(?Carbon $day = null): Collection
    {
        $targetDay = $day ?? Carbon::today();
        $dateString = $targetDay->toDateString();

        // One row per patient per day — joining raw appointments duplicates each
        // treatment_session row when a patient has multiple appointments that day,
        // inflating SUM(ts.received_payment).
        $appointmentDaySummary = DB::table('appointments')
            ->whereDate('started_at', $dateString)
            ->groupBy('patient_id')
            ->select('patient_id')
            ->selectRaw('MIN(started_at) as apt_started_at')
            ->selectRaw('MAX(COALESCE(completed_at, started_at)) as apt_ended_at');

        return DB::table('treatment_sessions as ts')
            ->join('treatment_infos as ti', 'ti.id', '=', 'ts.treatment_info_id')
            ->join('patients as p', 'p.id', '=', 'ti.patient_id')
            ->leftJoinSub($appointmentDaySummary, 'a', function ($join): void {
                $join->on('a.patient_id', '=', 'p.id');
            })
            ->where('ts.status', '!=', 'cancelled')
            ->where('ti.status', '!=', 'cancelled')
            ->whereDate('ts.created_at', $dateString)
            ->select([
                'ti.patient_id',
                'p.first_name',
                'p.last_name',
                DB::raw('MIN(COALESCE(a.apt_started_at, ts.session_date, ts.created_at)) as started_at'),
                DB::raw('MAX(COALESCE(a.apt_ended_at, ts.session_date, ts.created_at)) as completed_at'),
                DB::raw('SUM(ts.received_payment) as received_total'),
                DB::raw('MAX(ti.id) as latest_treatment_info_id'),
            ])
            ->groupBy('ti.patient_id', 'p.first_name', 'p.last_name')
            ->orderBy('started_at')
            ->get();
    }

    public function getCancelledSessionsForDay(Carbon $date): Collection
    {
        return DB::table('treatment_sessions as ts')
            ->join('treatment_infos as ti', 'ti.id', '=', 'ts.treatment_info_id')
            ->join('patients as p', 'p.id', '=', 'ti.patient_id')
            ->where('ts.status', '=', 'cancelled')
            ->whereDate('ts.cancelled_at', $date->toDateString())
            ->select([
                'ts.id as session_id',
                'ts.cancelled_at as cancelled_at',
                'ts.received_payment as refund_amount',
                'ti.id as treatment_info_id',
                'ti.description as treatment_description',
                'ti.patient_id',
                'p.first_name',
                'p.last_name',
            ])
            ->orderBy('ts.cancelled_at')
            ->orderBy('ts.id')
            ->get();
    }
}
