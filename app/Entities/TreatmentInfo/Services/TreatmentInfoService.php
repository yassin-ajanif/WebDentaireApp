<?php

namespace App\Entities\TreatmentInfo\Services;

use App\Entities\Appointment\Contracts\PatientLookupInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use App\Entities\TreatmentInfo\Enums\SessionStatus;
use App\Entities\TreatmentInfo\Enums\TreatmentStatus;
use App\Entities\TreatmentInfo\Models\Session;
use App\Entities\TreatmentInfo\Models\SessionCorrection;
use App\Entities\TreatmentInfo\Models\TreatmentCorrection;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class TreatmentInfoService implements TreatmentInfoServiceInterface
{
    public function __construct(
        private readonly PatientLookupInterface $patients,
    ) {}

    public function listForPatient(int $patientId): Collection
    {
        if (! $this->patients->exists($patientId)) {
            throw new DomainException(__('Unknown patient.'));
        }

        return TreatmentInfo::query()
            ->with(['sessions.corrections', 'corrections'])
            ->where('patient_id', $patientId)
            ->orderByDesc('id')
            ->get();
    }

    public function createTreatment(int $patientId, array $data): TreatmentInfo
    {
        if (! $this->patients->exists($patientId)) {
            throw new DomainException(__('Unknown patient.'));
        }

        return DB::transaction(function () use ($patientId, $data) {
            $globalPrice = $this->normalizeMoney($data['global_price'] ?? '0');
            $this->assertNonNegative($globalPrice, __('Global price must be zero or greater.'));

            $status = bccomp($globalPrice, '0.00', 2) === 0 ? TreatmentStatus::Paid : TreatmentStatus::Unpaid;

            return TreatmentInfo::query()->create([
                'patient_id' => $patientId,
                'description' => (string) ($data['description'] ?? ''),
                'global_price' => $globalPrice,
                'remaining_amount' => $globalPrice,
                'status' => $status,
            ]);
        });
    }

    public function updateTreatment(int $id, array $data): TreatmentInfo
    {
        return DB::transaction(function () use ($id, $data) {
            /** @var TreatmentInfo $row */
            $row = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($id);

            if ($row->status === TreatmentStatus::Cancelled) {
                throw new DomainException(__('Cannot update a cancelled treatment.'));
            }

            $globalPrice = $this->normalizeMoney($data['global_price'] ?? $row->global_price);
            $this->assertNonNegative($globalPrice, __('Global price must be zero or greater.'));

            $paid = $this->sumSessionPayments($row);
            if (bccomp($paid, $globalPrice, 2) === 1) {
                throw new DomainException(__('Global price cannot be less than total paid.'));
            }

            $remaining = bcsub($globalPrice, $paid, 2);
            $status = bccomp($remaining, '0.00', 2) === 0 ? TreatmentStatus::Paid : TreatmentStatus::Unpaid;

            $row->update([
                'description' => $data['description'] ?? $row->description,
                'global_price' => $globalPrice,
                'remaining_amount' => $remaining,
                'status' => $status,
            ]);

            return $row->fresh();
        });
    }

    public function deleteTreatment(int $id): void
    {
        TreatmentInfo::query()->whereKey($id)->delete();
    }

    public function cancelTreatment(int $id): void
    {
        TreatmentInfo::query()->whereKey($id)->update([
            'status' => TreatmentStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function createSession(int $treatmentId, array $data): Session
    {
        return DB::transaction(function () use ($treatmentId, $data) {
            /** @var TreatmentInfo $treatment */
            $treatment = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($treatmentId);

            if ($treatment->status === TreatmentStatus::Cancelled) {
                throw new DomainException(__('Cannot add session to a cancelled treatment.'));
            }

            $payment = $this->normalizeMoney($data['received_payment'] ?? '0');
            $this->assertNonNegative($payment, __('Received payment must be zero or greater.'));

            $currentPaid = $this->sumSessionPayments($treatment);
            $newPaid = bcadd($currentPaid, $payment, 2);

            if (bccomp($newPaid, (string) $treatment->global_price, 2) === 1) {
                throw new DomainException(__('Received payment exceeds remaining amount.'));
            }

            $session = Session::query()->create([
                'treatment_info_id' => $treatment->id,
                'session_date' => $data['session_date'] ?? now(),
                'received_payment' => $payment,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncRemainingAmount($treatment);

            return $session->fresh();
        });
    }

    public function updateSession(int $sessionId, array $data): Session
    {
        return DB::transaction(function () use ($sessionId, $data) {
            /** @var Session $session */
            $session = Session::query()->findOrFail($sessionId);

            if ($session->status === SessionStatus::Cancelled) {
                throw new DomainException(__('Cannot update a cancelled session.'));
            }

            /** @var TreatmentInfo $treatment */
            $treatment = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($session->treatment_info_id);

            if ($treatment->status === TreatmentStatus::Cancelled) {
                throw new DomainException(__('Cannot update session of a cancelled treatment.'));
            }

            $newPayment = $this->normalizeMoney($data['received_payment'] ?? $session->received_payment);
            $this->assertNonNegative($newPayment, __('Received payment must be zero or greater.'));

            $currentPaid = $this->sumSessionPayments($treatment);
            $paidWithoutCurrent = bcsub($currentPaid, (string) $session->received_payment, 2);
            $newPaid = bcadd($paidWithoutCurrent, $newPayment, 2);

            if (bccomp($newPaid, (string) $treatment->global_price, 2) === 1) {
                throw new DomainException(__('Received payment exceeds remaining amount.'));
            }

            $this->createSessionCorrection($session->id, [
                'session_date' => $session->session_date,
                'received_payment' => $newPayment,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $session->notes,
                'reason' => $data['reason'] ?? '',
            ], (isset($data['created_by']) && is_numeric($data['created_by'])) ? (int) $data['created_by'] : null);

            $session->update([
                'session_date' => $data['session_date'] ?? $session->session_date,
                'received_payment' => $newPayment,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $session->notes,
            ]);

            $this->syncRemainingAmount($treatment);

            return $session->fresh();
        });
    }

    public function cancelSession(int $sessionId): void
    {
        DB::transaction(function () use ($sessionId) {
            /** @var Session $session */
            $session = Session::query()->findOrFail($sessionId);

            /** @var TreatmentInfo $treatment */
            $treatment = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($session->treatment_info_id);

            if ($treatment->status === TreatmentStatus::Cancelled) {
                throw new DomainException(__('Cannot cancel session of a cancelled treatment.'));
            }

            $session->update([
                'status' => SessionStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $this->syncRemainingAmount($treatment);
        });
    }

    public function listCorrectionsForTreatment(int $treatmentId): Collection
    {
        return TreatmentCorrection::query()
            ->where('treatment_info_id', $treatmentId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function createCorrection(int $treatmentId, array $data, ?int $createdBy = null): TreatmentCorrection
    {
        return DB::transaction(function () use ($treatmentId, $data, $createdBy) {
            /** @var TreatmentInfo $treatment */
            $treatment = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($treatmentId);

            if ($treatment->status === TreatmentStatus::Cancelled) {
                throw new DomainException(__('Cannot correct a cancelled treatment.'));
            }

            $newGlobalPrice = $this->normalizeMoney($data['global_price'] ?? $treatment->global_price);
            $this->assertNonNegative($newGlobalPrice, __('Global price must be zero or greater.'));

            $newDescription = trim((string) ($data['description'] ?? $treatment->description));
            if ($newDescription === '') {
                throw new DomainException(__('Treatment type / description is required.'));
            }

            $reason = trim((string) ($data['reason'] ?? ''));
            if ($reason === '') {
                throw new DomainException(__('Correction reason is required.'));
            }

            $paid = $this->sumSessionPayments($treatment);
            if (bccomp($paid, $newGlobalPrice, 2) === 1) {
                throw new DomainException(__('Global price cannot be less than total paid.'));
            }

            $correction = TreatmentCorrection::query()->create([
                'treatment_info_id' => $treatment->id,
                'old_global_price' => (string) $treatment->global_price,
                'new_global_price' => $newGlobalPrice,
                'old_description' => (string) $treatment->description,
                'new_description' => $newDescription,
                'reason' => $reason,
                'created_by' => $createdBy,
                'created_at' => now(),
            ]);

            $remaining = bcsub($newGlobalPrice, $paid, 2);
            $status = bccomp($remaining, '0.00', 2) === 0 ? TreatmentStatus::Paid : TreatmentStatus::Unpaid;

            $treatment->update([
                'description' => $newDescription,
                'global_price' => $newGlobalPrice,
                'remaining_amount' => $remaining,
                'status' => $status,
            ]);

            return $correction->fresh();
        });
    }

    public function listCorrectionsForSession(int $sessionId): Collection
    {
        return SessionCorrection::query()
            ->where('treatment_session_id', $sessionId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function createSessionCorrection(int $sessionId, array $data, ?int $createdBy = null): SessionCorrection
    {
        return DB::transaction(function () use ($sessionId, $data, $createdBy) {
            /** @var Session $session */
            $session = Session::query()->findOrFail($sessionId);

            $newPayment = $this->normalizeMoney($data['received_payment'] ?? $session->received_payment);
            $this->assertNonNegative($newPayment, __('Received payment must be zero or greater.'));

            $reason = trim((string) ($data['reason'] ?? ''));
            if ($reason === '') {
                throw new DomainException(__('Session correction reason is required.'));
            }

            return SessionCorrection::query()->create([
                'treatment_session_id' => $session->id,
                'treatment_info_id' => $session->treatment_info_id,
                'old_session_date' => $session->session_date,
                'new_session_date' => $data['session_date'] ?? $session->session_date,
                'old_received_payment' => (string) $session->received_payment,
                'new_received_payment' => $newPayment,
                'old_notes' => $session->notes,
                'new_notes' => array_key_exists('notes', $data) ? $data['notes'] : $session->notes,
                'reason' => $reason,
                'created_by' => $createdBy,
                'created_at' => now(),
            ])->fresh();
        });
    }

    private function syncRemainingAmount(TreatmentInfo $treatment): void
    {
        if ($treatment->status === TreatmentStatus::Cancelled) {
            return;
        }

        $totalPaid = $this->sumSessionPayments($treatment->fresh('sessions'));
        $remaining = bcsub((string) $treatment->global_price, $totalPaid, 2);

        $status = bccomp($remaining, '0.00', 2) === 0 ? TreatmentStatus::Paid : TreatmentStatus::Unpaid;

        $treatment->update([
            'remaining_amount' => $remaining,
            'status' => $status,
        ]);
    }

    private function sumSessionPayments(TreatmentInfo $treatment): string
    {
        return $treatment->sessions
            ->filter(fn (Session $session) => $session->status === SessionStatus::Active->value)
            ->reduce(
                fn (string $carry, Session $session): string => bcadd($carry, (string) $session->received_payment, 2),
                '0.00'
            );
    }

    private function normalizeMoney(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function assertNonNegative(string $value, string $message): void
    {
        if (bccomp($value, '0.00', 2) === -1) {
            throw new DomainException($message);
        }
    }

    public function listCancellationsForDate(Carbon $date): SupportCollection
    {
        return DB::table('treatment_infos as ti')
            ->join('patients as p', 'p.id', '=', 'ti.patient_id')
            ->where('ti.status', '=', 'cancelled')
            ->whereDate('ti.cancelled_at', $date->toDateString())
            ->where(DB::raw('ti.global_price - ti.remaining_amount'), '>', 0)
            ->select([
                'ti.id as treatment_id',
                'ti.patient_id',
                'ti.description as treatment_description',
                'ti.global_price',
                'ti.remaining_amount',
                DB::raw('ti.global_price - ti.remaining_amount as refund_amount'),
                'p.first_name',
                'p.last_name',
            ])
            ->get()
            ->map(fn ($row) => [
                'patient_name' => trim($row->first_name . ' ' . $row->last_name) ?: '#' . $row->patient_id,
                'treatment_description' => $row->treatment_description,
                'refund_amount' => (float) $row->refund_amount,
                'treatment_id' => $row->treatment_id,
                'patient_id' => $row->patient_id,
            ]);
    }
}
