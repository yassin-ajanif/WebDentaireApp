<?php

namespace App\Entities\Patient\UnitTest;

use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\Patient\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientUpdateSyncsQueueDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_display_name_follows_linked_patient_after_update(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'telephone' => '0611111111',
            'notes' => null,
        ]);

        $appointment = Appointment::query()->create([
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::Waiting,
        ]);

        $appointment->load('patient');
        $this->assertSame('Old Name', $appointment->queueDisplayName());

        $svc = app(PatientServiceInterface::class);
        $svc->update($patient->id, [
            'first_name' => 'New',
            'last_name' => 'Patient',
            'telephone' => '0611111111',
            'notes' => null,
        ]);

        $appointment->refresh();
        $appointment->load('patient');
        $this->assertSame('New Patient', $appointment->queueDisplayName());
    }
}
