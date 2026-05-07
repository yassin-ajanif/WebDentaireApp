<?php

namespace App\Entities\Appointment\Contracts;

interface PatientLookupInterface
{
    public function exists(int $patientId): bool;
}
