<?php

namespace App\Entities\Chronology\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface ChronologyServiceInterface
{
    /**
     * @return Collection<int, \stdClass>
     */
    public function getSessionsForDay(?Carbon $day = null): Collection;

    /**
     * @return Collection<int, \stdClass>
     */
    public function getCancelledSessionsForDay(Carbon $date): Collection;
}
