<?php

namespace App\Entities\TreatmentInfo\Contracts;

use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use Illuminate\Database\Eloquent\Collection;

interface TreatmentInfoServiceInterface
{
    /**
     * @return Collection<int, TreatmentInfo>
     */
    public function listForPatient(int $patientId): Collection;

    public function create(int $patientId, array $data): TreatmentInfo;

    public function update(int $id, array $data): TreatmentInfo;

    public function delete(int $id): void;
}
