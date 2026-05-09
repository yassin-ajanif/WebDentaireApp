<?php

namespace App\Entities\Patient\Models;

use App\Entities\Appointment\Models\Appointment;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'first_name',
        'last_name',
        'telephone',
        'notes',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function treatmentInfos(): HasMany
    {
        return $this->hasMany(TreatmentInfo::class);
    }

    public function displayName(): string
    {
        $full = trim($this->first_name.' '.$this->last_name);

        return $full !== '' ? $full : '—';
    }
}
