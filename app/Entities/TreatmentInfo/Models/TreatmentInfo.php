<?php

namespace App\Entities\TreatmentInfo\Models;

use App\Entities\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentInfo extends Model
{
    protected $table = 'treatment_infos';

    protected $fillable = [
        'patient_id',
        'description',
        'global_price',
        'remaining_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'global_price' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'status' => \App\Entities\TreatmentInfo\Enums\TreatmentStatus::class,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class)->orderByDesc('session_date')->orderByDesc('id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(TreatmentCorrection::class)->orderByDesc('created_at')->orderByDesc('id');
    }
}
