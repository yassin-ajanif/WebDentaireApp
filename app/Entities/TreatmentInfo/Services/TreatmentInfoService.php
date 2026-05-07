<?php

namespace App\Entities\TreatmentInfo\Services;

use App\Entities\Appointment\Contracts\PatientLookupInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
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
            ->where('patient_id', $patientId)
            ->orderByDesc('id')
            ->get();
    }

    public function create(int $patientId, array $data): TreatmentInfo
    {
        if (! $this->patients->exists($patientId)) {
            throw new DomainException(__('Unknown patient.'));
        }

        return DB::transaction(function () use ($patientId, $data) {
            $qty = max(1, (int) ($data['quantity'] ?? 1));
            $unit = (string) ($data['unit_price'] ?? '0');
            $lineTotal = bcmul($unit, (string) $qty, 2);

            return TreatmentInfo::query()->create([
                'patient_id' => $patientId,
                'description' => $data['description'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $lineTotal,
            ]);
        });
    }

    public function update(int $id, array $data): TreatmentInfo
    {
        return DB::transaction(function () use ($id, $data) {
            $row = TreatmentInfo::query()->findOrFail($id);
            $qty = max(1, (int) ($data['quantity'] ?? $row->quantity));
            $unit = (string) ($data['unit_price'] ?? $row->unit_price);
            $lineTotal = bcmul($unit, (string) $qty, 2);

            $row->update([
                'description' => $data['description'] ?? $row->description,
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $lineTotal,
            ]);

            return $row->fresh();
        });
    }

    public function delete(int $id): void
    {
        TreatmentInfo::query()->whereKey($id)->delete();
    }
}
