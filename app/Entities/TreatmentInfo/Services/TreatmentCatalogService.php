<?php

namespace App\Entities\TreatmentInfo\Services;

use App\Entities\TreatmentInfo\Contracts\TreatmentCatalogServiceInterface;
use App\Models\ActivityCatalog;
use App\Models\TreatmentCatalog;
use Illuminate\Support\Collection;

class TreatmentCatalogService implements TreatmentCatalogServiceInterface
{
    public function getCatalog(): Collection
    {
        return TreatmentCatalog::with('activities')
            ->get()
            ->map(fn (TreatmentCatalog $catalog) => [
                'id' => $catalog->id,
                'name' => $catalog->name,
                'price' => $catalog->price ? (float) $catalog->price : null,
                'activities' => $catalog->activities->pluck('activity_name'),
            ]);
    }

    public function allTreatments(): Collection
    {
        return TreatmentCatalog::with('activities')->orderBy('name')->get();
    }

    public function findTreatment(int $id): ?TreatmentCatalog
    {
        return TreatmentCatalog::with('activities')->find($id);
    }

    public function createTreatment(array $data): TreatmentCatalog
    {
        return TreatmentCatalog::query()->create([
            'name' => $data['name'],
            'price' => $data['price'] ?? null,
        ]);
    }

    public function updateTreatment(int $id, array $data): TreatmentCatalog
    {
        $treatment = TreatmentCatalog::query()->findOrFail($id);
        $treatment->update([
            'name' => $data['name'],
            'price' => $data['price'] ?? null,
        ]);
        return $treatment->fresh();
    }

    public function deleteTreatment(int $id): void
    {
        TreatmentCatalog::query()->whereKey($id)->delete();
    }

    public function allActivities(): Collection
    {
        return ActivityCatalog::with('treatment')->orderBy('activity_name')->get();
    }

    public function createActivity(int $treatmentId, string $name): ActivityCatalog
    {
        return ActivityCatalog::query()->create([
            'treatment_catalog_id' => $treatmentId,
            'activity_name' => $name,
        ]);
    }

    public function updateActivity(int $id, string $name): ActivityCatalog
    {
        $activity = ActivityCatalog::query()->findOrFail($id);
        $activity->update(['activity_name' => $name]);
        return $activity->fresh();
    }

    public function deleteActivity(int $id): void
    {
        ActivityCatalog::query()->whereKey($id)->delete();
    }
}
