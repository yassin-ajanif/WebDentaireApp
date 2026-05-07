<?php

namespace App\Entities\Appointment\Contracts;

use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;

interface AppointmentServiceInterface
{
    public function createTicket(?int $patientId = null): Appointment;

    public function transitionStatus(int $appointmentId, AppointmentStatus $to): Appointment;

    public function isTransitionAllowed(AppointmentStatus $from, AppointmentStatus $to): bool;

    /**
     * @return Collection<int, Appointment>
     */
    public function listQueue(?AppointmentStatus $status = null): Collection;

    public function find(int $id): ?Appointment;
}
