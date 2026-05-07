<?php

namespace Tests\Feature\Appointment;

use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use App\Entities\Patient\Models\Patient;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppointmentTimelinePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_page_loads(): void
    {
        $this->get('/queue/timeline')->assertOk();
    }

    public function test_timeline_renders_table_headers_and_total(): void
    {
        $this->get('/queue/timeline')
            ->assertOk()
            ->assertSee('Patient')
            ->assertSee('Horaire')
            ->assertSee('Reçu (DH)')
            ->assertSee('Total à remettre au médecin');
    }

    public function test_timeline_binds_completed_appointments_data_in_hour_rows(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-07 10:00:00'));

        $completedPatient = Patient::query()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Karim',
            'telephone' => '0611000003',
            'notes' => null,
        ]);
        $incompletePatient = Patient::query()->create([
            'first_name' => 'Salim',
            'last_name' => 'Test',
            'telephone' => '0611000004',
            'notes' => null,
        ]);

        Appointment::query()->create([
            'patient_id' => $completedPatient->id,
            'status' => AppointmentStatus::Done,
            'started_at' => Carbon::parse('2026-05-07 14:41:00'),
            'completed_at' => Carbon::parse('2026-05-07 14:42:00'),
        ]);
        Appointment::query()->create([
            'patient_id' => $incompletePatient->id,
            'status' => AppointmentStatus::InProgress,
            'started_at' => Carbon::parse('2026-05-07 11:00:00'),
            'completed_at' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $completedPatient->id,
            'description' => 'Détartrage',
            'global_price' => 200,
            'remaining_amount' => 80,
        ]);

        DB::table('treatment_sessions')->insert([
            'treatment_info_id' => $treatment->id,
            'session_date' => Carbon::parse('2026-05-07 14:42:00'),
            'received_payment' => 120,
            'notes' => 'Paiement séance',
            'created_at' => Carbon::parse('2026-05-07 14:50:00'),
            'updated_at' => Carbon::parse('2026-05-07 14:50:00'),
        ]);

        $this->get('/queue/timeline')
            ->assertOk()
            ->assertSee('Nadia Karim')
            ->assertSee('14:41 - 14:42')
            ->assertSee('120.00')
            ->assertSee('/patients/'.$completedPatient->id.'/treatments')
            ->assertDontSee('Salim Test');

        Carbon::setTestNow();
    }
}
