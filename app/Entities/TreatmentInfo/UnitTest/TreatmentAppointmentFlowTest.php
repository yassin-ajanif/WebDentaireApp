<?php

namespace App\Entities\TreatmentInfo\UnitTest;

use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use App\Entities\Auth\Models\User;
use App\Entities\Patient\Models\Patient;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use App\Entities\TreatmentInfo\Ui\Livewire\TreatmentLinesPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class TreatmentAppointmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_treatment_page_shows_finish_button_for_in_progress_appointment(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Karim',
            'telephone' => '0611777000',
            'notes' => null,
        ]);

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::InProgress,
            'started_at' => now()->subMinutes(5),
            'completed_at' => null,
        ]);

        $appointmentId = Appointment::query()->where('patient_id', $patient->id)->value('id');

        Livewire::withQueryParams(['appointment' => $appointmentId])
            ->test(TreatmentLinesPage::class, ['patient' => $patient->id])
            ->assertSee(__('Terminer la consultation'));
    }

    public function test_saving_session_then_finish_consultation_marks_appointment_done_and_redirects_to_queue(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'Salim',
            'last_name' => 'Test',
            'telephone' => '0611666000',
            'notes' => null,
        ]);

        $appointment = Appointment::query()->create([
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::InProgress,
            'started_at' => now()->subMinutes(15),
            'completed_at' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Détartrage',
            'global_price' => 300,
            'remaining_amount' => 300,
        ]);

        Livewire::withQueryParams(['appointment' => $appointment->id])
            ->test(TreatmentLinesPage::class, ['patient' => $patient->id])
            ->set("sessionForms.{$treatment->id}", [
                'session_date' => now()->format('Y-m-d\TH:i'),
                'received_payment' => '120.00',
                'notes' => 'Paiement séance',
            ])
            ->call('saveSession', $treatment->id);

        $this->assertSame(AppointmentStatus::InProgress, $appointment->fresh()->status);

        Livewire::withQueryParams(['appointment' => $appointment->id])
            ->test(TreatmentLinesPage::class, ['patient' => $patient->id])
            ->call('finishAppointment')
            ->assertRedirect(route('queue.index'));

        $this->assertSame(AppointmentStatus::Done, $appointment->fresh()->status);
    }

    public function test_treatment_page_renders_correction_history_after_edit(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Karim',
            'telephone' => '0611777999',
            'notes' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Blanchiment',
            'global_price' => 400,
            'remaining_amount' => 400,
        ]);

        Livewire::test(TreatmentLinesPage::class, ['patient' => $patient->id])
            ->call('startEditTreatment', $treatment->id)
            ->set('treatmentDescription', 'Blanchiment premium')
            ->set('globalPrice', '450.00')
            ->set('correctionReason', 'Mise a jour du plan')
            ->call('saveTreatment');

        Livewire::test(TreatmentLinesPage::class, ['patient' => $patient->id])
            ->set("expandedTreatments.{$treatment->id}", true)
            ->assertSee(__('Treatment correction history'))
            ->assertSee('Mise a jour du plan')
            ->assertSee('Blanchiment premium');
    }

    public function test_query_param_treatment_expands_selected_treatment_card(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Karim',
            'telephone' => '0611777888',
            'notes' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Orthodontie',
            'global_price' => 700,
            'remaining_amount' => 700,
        ]);

        Livewire::withQueryParams(['treatment' => $treatment->id])
            ->test(TreatmentLinesPage::class, ['patient' => $patient->id])
            ->assertSet("expandedTreatments.{$treatment->id}", true)
            ->assertSee(__('Dates'));
    }

    public function test_updating_session_requires_session_correction_reason(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Karim',
            'telephone' => '0611777444',
            'notes' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Orthodontie',
            'global_price' => 600,
            'remaining_amount' => 500,
        ]);

        $sessionId = DB::table('treatment_sessions')->insertGetId([
            'treatment_info_id' => $treatment->id,
            'session_date' => now()->subDay(),
            'received_payment' => 100,
            'notes' => 'old note',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Livewire::test(TreatmentLinesPage::class, ['patient' => $patient->id])
            ->call('startEditSession', $treatment->id, $sessionId)
            ->set("sessionForms.{$treatment->id}.received_payment", '120.00')
            ->set("sessionForms.{$treatment->id}.notes", 'new note')
            ->call('saveSession', $treatment->id)
            ->assertHasErrors(['sessionCorrectionReason' => 'required']);
    }

    public function test_treatment_page_highlights_sessions_matching_highlight_date(): void
    {
        $patient = Patient::query()->create([
            'first_name' => 'Nadia',
            'last_name' => 'Karim',
            'telephone' => '0611777333',
            'notes' => null,
        ]);

        $treatment = TreatmentInfo::query()->create([
            'patient_id' => $patient->id,
            'description' => 'Orthodontie',
            'global_price' => 1000,
            'remaining_amount' => 800,
        ]);

        DB::table('treatment_sessions')->insert([
            [
                'treatment_info_id' => $treatment->id,
                'session_date' => '2026-05-08 13:33:00',
                'received_payment' => 90,
                'notes' => 'session target',
                'created_at' => '2026-05-08 13:33:00',
                'updated_at' => '2026-05-08 13:33:00',
            ],
            [
                'treatment_info_id' => $treatment->id,
                'session_date' => '2026-05-07 13:33:00',
                'received_payment' => 100,
                'notes' => 'session old',
                'created_at' => '2026-05-07 13:33:00',
                'updated_at' => '2026-05-07 13:33:00',
            ],
        ]);

        $this->get('/patients/'.$patient->id.'/treatments?treatment='.$treatment->id.'&highlight_date=2026-05-08')
            ->assertOk()
            ->assertSee('data-highlighted-session="true"', false)
            ->assertSee('session target')
            ->assertSee('session old');
    }
}
