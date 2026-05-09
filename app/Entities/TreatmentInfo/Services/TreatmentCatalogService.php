<?php

namespace App\Entities\TreatmentInfo\Services;

use App\Entities\TreatmentInfo\Contracts\TreatmentCatalogServiceInterface;
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
}
