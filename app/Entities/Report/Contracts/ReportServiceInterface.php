<?php

namespace App\Entities\Report\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface ReportServiceInterface
{
    public function revenueByPeriod(Carbon $from, Carbon $to): Collection;

    public function patientCredits(): Collection;
}
