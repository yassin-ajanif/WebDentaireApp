<?php

namespace App\Entities\TreatmentInfo\Contracts;

use App\Models\ActivityCatalog;
use App\Models\TreatmentCatalog;
use Illuminate\Support\Collection;

interface TreatmentCatalogServiceInterface
{
    public function getCatalog(): Collection;

    public function allTreatments(): Collection;

    public function findTreatment(int $id): ?TreatmentCatalog;

    public function createTreatment(array $data): TreatmentCatalog;

    public function updateTreatment(int $id, array $data): TreatmentCatalog;

    public function deleteTreatment(int $id): void;

    /** @return Collection<int, ActivityCatalog> */
    public function allActivities(): Collection;

    public function createActivity(int $treatmentId, string $name): ActivityCatalog;

    public function updateActivity(int $id, string $name): ActivityCatalog;

    public function deleteActivity(int $id): void;
}
