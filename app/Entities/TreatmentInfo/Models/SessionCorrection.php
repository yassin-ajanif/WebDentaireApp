<?php

namespace App\Entities\TreatmentInfo\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionCorrection extends Model
{
    public $timestamps = false;

    protected $table = 'treatment_session_corrections';

    protected $fillable = [
        'treatment_session_id',
        'treatment_info_id',
        'old_session_date',
        'new_session_date',
        'old_received_payment',
        'new_received_payment',
        'old_notes',
        'new_notes',
        'reason',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_session_date' => 'datetime',
            'new_session_date' => 'datetime',
            'old_received_payment' => 'decimal:2',
            'new_received_payment' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'treatment_session_id');
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
