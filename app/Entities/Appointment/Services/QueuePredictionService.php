<?php

namespace App\Entities\Appointment\Services;

use App\Entities\Appointment\Contracts\QueuePredictionServiceInterface;
use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use App\Entities\Setting\Contracts\QueueSettingsServiceInterface;

class QueuePredictionService implements QueuePredictionServiceInterface
{
    public function __construct(
        private readonly QueueSettingsServiceInterface $queueSettings,
    ) {}

    public function estimatedMinutesToClearQueue(): ?int
    {
        $avg = $this->queueSettings->getQueuePredictionConfig()['average_consultation_minutes'];

        $ahead = Appointment::query()
            ->createdOn()
            ->whereIn('status', [AppointmentStatus::Waiting, AppointmentStatus::InProgress])
            ->count();

        if ($ahead === 0) {
            return 0;
        }

        return $ahead * $avg;
    }
}
