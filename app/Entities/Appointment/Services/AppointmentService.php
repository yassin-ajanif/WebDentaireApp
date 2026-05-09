<?php

namespace App\Entities\Appointment\Services;

use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
use App\Entities\Appointment\Contracts\PatientLookupInterface;
use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService implements AppointmentServiceInterface
{
    public function __construct(
        private readonly PatientLookupInterface $patients,
    ) {}

    public function createTicket(?int $patientId = null): Appointment
    {
        if ($patientId !== null && ! $this->patients->exists($patientId)) {
            throw new DomainException(__('Unknown patient.'));
        }

        if (Appointment::query()->whereDate('created_at', today())->exists()) {
            throw new DomainException(__('Un rendez-vous existe déjà aujourd\'hui.'));
        }

        return DB::transaction(function () use ($patientId) {
            return Appointment::query()->create([
                'patient_id' => $patientId,
                'status' => AppointmentStatus::Waiting,
            ]);
        });
    }

    public function transitionStatus(int $appointmentId, AppointmentStatus $to): Appointment
    {
        return DB::transaction(function () use ($appointmentId, $to) {
            /** @var Appointment $appointment */
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointmentId);
            $from = $appointment->status;

            if (! $this->canTransition($from, $to)) {
                throw new DomainException(__('Invalid status transition from :from to :to.', [
                    'from' => $from->value,
                    'to' => $to->value,
                ]));
            }

            $appointment->status = $to;
            $this->applyTimestampsForTransition($appointment, $to);

            $appointment->save();

            return $appointment->fresh();
        });
    }

    public function listQueue(?AppointmentStatus $status = null): Collection
    {
        $q = Appointment::query()
            ->with('patient')
            ->createdOn()
            ->orderBy('created_at')
            ->orderBy('id');

        if ($status !== null) {
            $q->where('status', $status);
        }

        return $q->get();
    }

    public function find(int $id): ?Appointment
    {
        return Appointment::query()->find($id);
    }

    public function isTransitionAllowed(AppointmentStatus $from, AppointmentStatus $to): bool
    {
        return $this->canTransition($from, $to);
    }

    private function applyTimestampsForTransition(Appointment $appointment, AppointmentStatus $to): void
    {
        $now = now();

        match ($to) {
            AppointmentStatus::Waiting => $this->resetActiveTimestamps($appointment),
            AppointmentStatus::InProgress => $this->markInProgress($appointment, $now),
            AppointmentStatus::Done => $appointment->completed_at = $now,
            AppointmentStatus::Cancelled => $this->markCancelledIfNeeded($appointment, $now),
        };
    }

    private function resetActiveTimestamps(Appointment $appointment): void
    {
        $appointment->started_at = null;
        $appointment->completed_at = null;
    }

    private function markInProgress(Appointment $appointment, Carbon $now): void
    {
        $appointment->completed_at = null;
        $appointment->started_at = $now;
    }

    private function markCancelledIfNeeded(Appointment $appointment, Carbon $now): void
    {
        if ($appointment->completed_at === null) {
            $appointment->completed_at = $now;
        }
    }

    private function canTransition(AppointmentStatus $from, AppointmentStatus $to): bool
    {
        if ($from === $to) {
            return false;
        }

        return match ($from) {
            AppointmentStatus::Waiting => in_array($to, [
                AppointmentStatus::InProgress,
                AppointmentStatus::Cancelled,
            ], true),
            AppointmentStatus::InProgress => in_array($to, [
                AppointmentStatus::Waiting,
                AppointmentStatus::Done,
                AppointmentStatus::Cancelled,
            ], true),
            AppointmentStatus::Done => in_array($to, [
                AppointmentStatus::Waiting,
                AppointmentStatus::InProgress,
                AppointmentStatus::Cancelled,
            ], true),
            AppointmentStatus::Cancelled => in_array($to, [
                AppointmentStatus::Waiting,
                AppointmentStatus::InProgress,
            ], true),
        };
    }
}
