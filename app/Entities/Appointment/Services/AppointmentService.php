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

    public function listCompletedTimeline(?Carbon $day = null): Collection
    {
        $targetDay = $day ?? Carbon::today();

        $paymentsByPatientDay = DB::table('treatment_sessions as ts')
            ->join('treatment_infos as ti', 'ti.id', '=', 'ts.treatment_info_id')
            ->selectRaw('ti.patient_id as patient_id, DATE(ts.created_at) as paid_date, SUM(ts.received_payment) as received_total')
            ->groupBy('ti.patient_id', DB::raw('DATE(ts.created_at)'));

        return Appointment::query()
            ->with('patient')
            ->select('appointments.*')
            ->selectRaw('COALESCE(payments.received_total, 0) as received_total')
            ->leftJoinSub($paymentsByPatientDay, 'payments', function ($join): void {
                $join->on('payments.patient_id', '=', 'appointments.patient_id')
                    ->whereRaw('DATE(appointments.completed_at) = payments.paid_date');
            })
            ->whereNotNull('appointments.started_at')
            ->whereNotNull('appointments.completed_at')
            ->whereDate('appointments.completed_at', $targetDay->toDateString())
            ->orderBy('appointments.started_at')
            ->orderBy('appointments.id')
            ->get();
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
