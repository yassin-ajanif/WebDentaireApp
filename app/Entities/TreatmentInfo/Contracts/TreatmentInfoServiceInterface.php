<?php

namespace App\Entities\TreatmentInfo\Contracts;

use App\Entities\TreatmentInfo\Models\Session;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use Illuminate\Database\Eloquent\Collection;

interface TreatmentInfoServiceInterface
{
    /**
     * @return Collection<int, TreatmentInfo>
     */
    public function listForPatient(int $patientId): Collection;

    public function createTreatment(int $patientId, array $data): TreatmentInfo;

    public function updateTreatment(int $id, array $data): TreatmentInfo;

    public function deleteTreatment(int $id): void;

    public function createSession(int $treatmentId, array $data): Session;

    public function updateSession(int $sessionId, array $data): Session;

    public function deleteSession(int $sessionId): void;
}
