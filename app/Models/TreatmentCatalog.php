<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentCatalog extends Model
{
    protected $table = 'treatment_catalog';

    protected $fillable = ['name'];

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityCatalog::class, 'treatment_catalog_id');
    }
}
