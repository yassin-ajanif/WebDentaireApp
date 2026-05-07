<?php

namespace App\Entities\Appointment\Contracts;

interface QueuePredictionServiceInterface
{
    /**
     * Estimated minutes until the current queue is cleared (rough heuristic).
     */
    public function estimatedMinutesToClearQueue(): ?int;
}
