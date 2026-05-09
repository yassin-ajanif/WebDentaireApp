<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityCatalog extends Model
{
    protected $table = 'activity_catalog';

    protected $fillable = ['treatment_catalog_id', 'activity_name'];

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(TreatmentCatalog::class, 'treatment_catalog_id');
    }
}
