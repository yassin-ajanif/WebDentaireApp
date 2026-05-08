<?php

namespace App\Entities\TreatmentInfo\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentCorrection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'treatment_info_id',
        'old_global_price',
        'new_global_price',
        'old_description',
        'new_description',
        'reason',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_global_price' => 'decimal:2',
            'new_global_price' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(TreatmentInfo::class, 'treatment_info_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
