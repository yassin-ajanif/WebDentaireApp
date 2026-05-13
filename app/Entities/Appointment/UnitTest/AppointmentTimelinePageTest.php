<?php

namespace App\Entities\Appointment\UnitTest;

use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use App\Entities\Auth\Models\User;
use App\Entities\Chronology\Contracts\ChronologyServiceInterface;
use App\Entities\Patient\Models\Patient;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use App\Entities\TreatmentInfo\Enums\TreatmentStatus;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppointmentTimelinePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

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
            ->assertSee('Net à remettre au médecin');
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
            ->assertSee('treatment='.$treatment->id)
            ->assertSee('highlight_date=2026-05-07')
            ->assertDontSee('Salim Test');

        Carbon::setTestNow();
    }

    public function test_timeline_does_not_double_session_total_when_patient_has_multiple_appointments_same_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-10 12:00:00'));

        $patient = Patient::query()->create([
            'first_name' => 'Ahmed',
            'last_name' => 'DoubleAppt',
            'telephone' => '0611000999',
            'notes' => null,
        ]);

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::Done,
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'completed_at' => Carbon::parse('2026-05-10 09:30:00'),
        ]);
        Appointment::query()->create([
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::Done,
            'started_at' => Carbon::parse('2026-05-10 11:00:00'),
            'completed_at' => Carbon::parse('2026-05-10 11:15:00'),
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Consultation',
            'global_price' => 500,
            'remaining_amount' => 200,
        ]);

        DB::table('treatment_sessions')->insert([
            'treatment_info_id' => $treatment->id,
            'session_date' => Carbon::parse('2026-05-10 10:00:00'),
            'received_payment' => 300,
            'notes' => 'Paiement unique',
            'created_at' => Carbon::parse('2026-05-10 10:05:00'),
            'updated_at' => Carbon::parse('2026-05-10 10:05:00'),
        ]);

        $response = $this->get('/queue/timeline');
        $response->assertOk();
        $response->assertSee('300.00', false);
        $response->assertDontSee('600.00', false);

        Carbon::setTestNow();
    }

    public function test_chronology_received_aggregate_includes_cancelled_session_amounts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20 10:00:00'));

        $patient = Patient::query()->create([
            'first_name' => 'Samir',
            'last_name' => 'CancelSameDay',
            'telephone' => '0611000888',
            'notes' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Détartrage supra-gingival',
            'global_price' => 100,
            'remaining_amount' => 70,
        ]);

        DB::table('treatment_sessions')->insert([
            'treatment_info_id' => $treatment->id,
            'session_date' => Carbon::parse('2026-05-20 00:22:00'),
            'received_payment' => 30,
            'notes' => 'Paiement',
            'status' => 'cancelled',
            'cancelled_at' => Carbon::parse('2026-05-20 00:25:00'),
            'created_at' => Carbon::parse('2026-05-20 00:21:00'),
            'updated_at' => Carbon::parse('2026-05-20 00:25:00'),
        ]);

        $chronology = app(ChronologyServiceInterface::class);
        $day = \Illuminate\Support\Carbon::parse('2026-05-20');

        $sessionRows = $chronology->getSessionsForDay($day);
        $this->assertCount(1, $sessionRows);
        $this->assertSame(30.0, (float) $sessionRows->first()->received_total);

        $cancelledRows = $chronology->getCancelledSessionsForDay($day);
        $this->assertCount(1, $cancelledRows);
        $this->assertSame(30.0, (float) $cancelledRows->first()->refund_amount);

        Carbon::setTestNow();
    }

    public function test_chronology_received_includes_sessions_when_treatment_cancelled_same_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00'));

        $patient = Patient::query()->create([
            'first_name' => 'Lina',
            'last_name' => 'TraitCancel',
            'telephone' => '0611000777',
            'notes' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Bridge',
            'global_price' => 200,
            'remaining_amount' => 170,
            'status' => TreatmentStatus::Unpaid,
        ]);

        DB::table('treatment_sessions')->insert([
            'treatment_info_id' => $treatment->id,
            'session_date' => Carbon::parse('2026-06-01 10:00:00'),
            'received_payment' => 30,
            'notes' => 'Acompte',
            'status' => 'active',
            'cancelled_at' => null,
            'created_at' => Carbon::parse('2026-06-01 10:05:00'),
            'updated_at' => Carbon::parse('2026-06-01 10:05:00'),
        ]);

        TreatmentInfo::query()->whereKey($treatment->id)->update([
            'status' => TreatmentStatus::Cancelled,
            'cancelled_at' => Carbon::parse('2026-06-01 15:00:00'),
        ]);

        $day = \Illuminate\Support\Carbon::parse('2026-06-01');
        $chronology = app(ChronologyServiceInterface::class);
        $sessionRows = $chronology->getSessionsForDay($day);
        $this->assertCount(1, $sessionRows);
        $this->assertSame(30.0, (float) $sessionRows->first()->received_total);

        $treatmentCancellations = app(TreatmentInfoServiceInterface::class)->listCancellationsForDate($day);
        $this->assertCount(1, $treatmentCancellations);
        $this->assertSame(30.0, $treatmentCancellations->first()['refund_amount']);

        Carbon::setTestNow();
    }
}
