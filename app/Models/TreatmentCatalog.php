<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentCatalog extends Model
{
    protected $table = 'treatment_catalog';

    protected $fillable = ['name', 'price'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityCatalog::class, 'treatment_catalog_id');
    }
}
