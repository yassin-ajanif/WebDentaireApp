<?php

namespace App\Entities\TreatmentInfo\Contracts;

use Illuminate\Support\Collection;

interface TreatmentCatalogServiceInterface
{
    public function getCatalog(): Collection;
}
