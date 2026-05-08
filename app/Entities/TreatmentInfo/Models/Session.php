<?php

namespace App\Entities\TreatmentInfo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    protected $table = 'treatment_sessions';

    protected $fillable = [
        'treatment_info_id',
        'session_date',
        'received_payment',
        'notes',
        'status',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'datetime',
            'received_payment' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(TreatmentInfo::class, 'treatment_info_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(SessionCorrection::class, 'treatment_session_id')->orderByDesc('created_at')->orderByDesc('id');
    }
}
