<?php

namespace App\Entities\TreatmentInfo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'treatment_sessions';

    protected $fillable = [
        'treatment_info_id',
        'session_date',
        'received_payment',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'datetime',
            'received_payment' => 'decimal:2',
        ];
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(TreatmentInfo::class, 'treatment_info_id');
    }
}
