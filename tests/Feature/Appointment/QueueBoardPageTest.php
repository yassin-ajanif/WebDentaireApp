<?php

namespace Tests\Feature\Appointment;

use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Appointment\Models\Appointment;
use App\Entities\Appointment\Ui\Livewire\QueueBoardPage;
use App\Entities\Patient\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QueueBoardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_page_loads(): void
    {
        $this->get('/queue')->assertOk();
    }

    public function test_new_dialog_creates_patient_and_queue_ticket_shows_patient_name(): void
    {
        Livewire::test(QueueBoardPage::class)
            ->set('showNewDialog', true)
            ->set('newName', 'Jean Dupont')
            ->set('newTelephone', '0611223344')
            ->call('saveNewDialog')
            ->assertHasNoErrors();

        $patient = Patient::query()->where('telephone', '0611223344')->first();
        $this->assertNotNull($patient);
        $this->assertSame('Jean Dupont', $patient->first_name);

        $appointment = Appointment::query()->where('patient_id', $patient->id)->with('patient')->latest('id')->first();
        $this->assertNotNull($appointment);
        $this->assertSame('Jean Dupont', $appointment->queueDisplayName());
    }

    public function test_duplicate_telephone_shows_confirm_then_adds_to_queue_on_confirm(): void
    {
        Patient::query()->create([
            'first_name' => 'Existant',
            'last_name' => 'Patient',
            'telephone' => '0611999900',
            'notes' => null,
        ]);

        $component = Livewire::test(QueueBoardPage::class)
            ->set('showNewDialog', true)
            ->set('newName', 'Autre')
            ->set('newTelephone', '0611999900')
            ->call('saveNewDialog')
            ->assertSet('showExistingPatientConfirm', true)
            ->assertSet('existingPatientDisplayName', 'Existant Patient')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('appointments', 0);

        $patientId = Patient::query()->where('telephone', '0611999900')->value('id');

        $component->call('confirmAddExistingPatientToQueue')
            ->assertSet('showNewDialog', false)
            ->assertSet('showExistingPatientConfirm', false);

        $this->assertDatabaseCount('appointments', 1);
        $this->assertSame(1, Appointment::query()->where('patient_id', $patientId)->count());
    }

    public function test_queue_board_only_shows_tickets_created_today(): void
    {
        $yesterdayPatient = Patient::query()->create([
            'first_name' => 'Hier',
            'last_name' => 'Patient',
            'telephone' => '0611000001',
            'notes' => null,
        ]);
        $yesterdayAppt = Appointment::query()->create([
            'patient_id' => $yesterdayPatient->id,
            'status' => AppointmentStatus::Waiting,
        ]);
        $yesterdayAppt->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

        $todayPatient = Patient::query()->create([
            'first_name' => 'Aujourd',
            'last_name' => 'hui',
            'telephone' => '0611000002',
            'notes' => null,
        ]);
        Appointment::query()->create([
            'patient_id' => $todayPatient->id,
            'status' => AppointmentStatus::Waiting,
        ]);

        Livewire::test(QueueBoardPage::class)
            ->assertSee('Aujourd')
            ->assertDontSee('Hier');
    }
}
