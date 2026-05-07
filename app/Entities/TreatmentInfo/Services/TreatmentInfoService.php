<?php

namespace App\Entities\TreatmentInfo\Services;

use App\Entities\Appointment\Contracts\PatientLookupInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use App\Entities\TreatmentInfo\Models\Session;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
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
            ->with(['sessions'])
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

            return TreatmentInfo::query()->create([
                'patient_id' => $patientId,
                'description' => (string) ($data['description'] ?? ''),
                'global_price' => $globalPrice,
                'remaining_amount' => $globalPrice,
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

            $globalPrice = $this->normalizeMoney($data['global_price'] ?? $row->global_price);
            $this->assertNonNegative($globalPrice, __('Global price must be zero or greater.'));

            $paid = $this->sumSessionPayments($row);
            if (bccomp($paid, $globalPrice, 2) === 1) {
                throw new DomainException(__('Global price cannot be less than total paid.'));
            }

            $remaining = bcsub($globalPrice, $paid, 2);
            $row->update([
                'description' => $data['description'] ?? $row->description,
                'global_price' => $globalPrice,
                'remaining_amount' => $remaining,
            ]);

            return $row->fresh();
        });
    }

    public function deleteTreatment(int $id): void
    {
        TreatmentInfo::query()->whereKey($id)->delete();
    }

    public function createSession(int $treatmentId, array $data): Session
    {
        return DB::transaction(function () use ($treatmentId, $data) {
            /** @var TreatmentInfo $treatment */
            $treatment = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($treatmentId);

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

            /** @var TreatmentInfo $treatment */
            $treatment = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($session->treatment_info_id);

            $newPayment = $this->normalizeMoney($data['received_payment'] ?? $session->received_payment);
            $this->assertNonNegative($newPayment, __('Received payment must be zero or greater.'));

            $currentPaid = $this->sumSessionPayments($treatment);
            $paidWithoutCurrent = bcsub($currentPaid, (string) $session->received_payment, 2);
            $newPaid = bcadd($paidWithoutCurrent, $newPayment, 2);

            if (bccomp($newPaid, (string) $treatment->global_price, 2) === 1) {
                throw new DomainException(__('Received payment exceeds remaining amount.'));
            }

            $session->update([
                'session_date' => $data['session_date'] ?? $session->session_date,
                'received_payment' => $newPayment,
                'notes' => $data['notes'] ?? $session->notes,
            ]);

            $this->syncRemainingAmount($treatment);

            return $session->fresh();
        });
    }

    public function deleteSession(int $sessionId): void
    {
        DB::transaction(function () use ($sessionId) {
            /** @var Session $session */
            $session = Session::query()->findOrFail($sessionId);

            /** @var TreatmentInfo $treatment */
            $treatment = TreatmentInfo::query()
                ->lockForUpdate()
                ->with('sessions')
                ->findOrFail($session->treatment_info_id);

            $session->delete();
            $this->syncRemainingAmount($treatment);
        });
    }

    private function syncRemainingAmount(TreatmentInfo $treatment): void
    {
        $totalPaid = $this->sumSessionPayments($treatment->fresh('sessions'));
        $remaining = bcsub((string) $treatment->global_price, $totalPaid, 2);

        $treatment->update([
            'remaining_amount' => $remaining,
        ]);
    }

    private function sumSessionPayments(TreatmentInfo $treatment): string
    {
        return $treatment->sessions
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
}
