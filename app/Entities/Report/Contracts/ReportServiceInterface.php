<?php

namespace App\Entities\Report\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface ReportServiceInterface
{
    public function revenueByPeriod(Carbon $from, Carbon $to): Collection;

    public function patientCredits(): Collection;

    public function treatmentCorrectionsByPeriod(Carbon $from, Carbon $to): Collection;

    public function sessionCorrectionsByPeriod(Carbon $from, Carbon $to): Collection;

    public function cancelledTreatmentsByPeriod(Carbon $from, Carbon $to): Collection;

    public function cancelledSessionsByPeriod(Carbon $from, Carbon $to): Collection;
}
