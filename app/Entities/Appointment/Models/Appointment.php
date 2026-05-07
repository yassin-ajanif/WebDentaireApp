<?php

namespace App\Entities\Appointment\Models;

use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Tickets created on a calendar day (app timezone). Defaults to today for the daily queue board.
     */
    public function scopeCreatedOn(Builder $query, ?Carbon $date = null): void
    {
        $query->whereDate('created_at', $date ?? Carbon::today());
    }

    /**
     * Name shown on the queue: from linked patient (appointments.patient_id → patients).
     */
    public function queueDisplayName(): string
    {
        $patient = $this->patient;
        if ($patient !== null) {
            $full = trim($patient->first_name.' '.$patient->last_name);

            return $full !== '' ? $full : '—';
        }

        return '—';
    }
}
