<?php

namespace App\Entities\TreatmentInfo\Contracts;

use App\Entities\TreatmentInfo\Models\Session;
use App\Entities\TreatmentInfo\Models\SessionCorrection;
use App\Entities\TreatmentInfo\Models\TreatmentCorrection;
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

    public function cancelTreatment(int $id): void;

    public function createSession(int $treatmentId, array $data): Session;

    public function updateSession(int $sessionId, array $data): Session;

    public function cancelSession(int $sessionId): void;

    /**
     * @return Collection<int, TreatmentCorrection>
     */
    public function listCorrectionsForTreatment(int $treatmentId): Collection;

    public function createCorrection(int $treatmentId, array $data, ?int $createdBy = null): TreatmentCorrection;

    /**
     * @return Collection<int, SessionCorrection>
     */
    public function listCorrectionsForSession(int $sessionId): Collection;

    public function createSessionCorrection(int $sessionId, array $data, ?int $createdBy = null): SessionCorrection;
}
