<?php

namespace App\Entities\Appointment\Ui\Livewire;

use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
use App\Entities\Appointment\Contracts\QueuePredictionServiceInterface;
use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\Patient\Models\Patient;
use DomainException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QueueBoardPage extends Component
{
    public bool $showNewDialog = false;

    public string $newName = '';

    public null|int|string $newAge = null;

    public string $newAddress = '';

    public string $newTelephone = '';

    public bool $showExistingPatientConfirm = false;

    public ?int $existingPatientIdForConfirm = null;

    public string $existingPatientDisplayName = '';

    private function appointments(): AppointmentServiceInterface
    {
        return app(AppointmentServiceInterface::class);
    }

    private function patients(): PatientServiceInterface
    {
        return app(PatientServiceInterface::class);
    }

    private function prediction(): QueuePredictionServiceInterface
    {
        return app(QueuePredictionServiceInterface::class);
    }

    public function openNewDialog(): void
    {
        $this->resetValidation();
        $this->resetExistingPatientConfirm();
        $this->showNewDialog = true;
    }

    public function closeNewDialog(): void
    {
        $this->showNewDialog = false;
        $this->resetExistingPatientConfirm();
        $this->reset(['newName', 'newAge', 'newAddress', 'newTelephone']);
    }

    public function cancelExistingPatientConfirm(): void
    {
        $this->resetExistingPatientConfirm();
    }

    public function confirmAddExistingPatientToQueue(): void
    {
        if ($this->existingPatientIdForConfirm === null) {
            return;
        }

        try {
            $this->appointments()->createTicket($this->existingPatientIdForConfirm);
            $this->closeNewDialog();
            session()->flash('status', __('Nouveau numéro enregistré.'));
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
            $this->resetExistingPatientConfirm();
        }
    }

    private function resetExistingPatientConfirm(): void
    {
        $this->showExistingPatientConfirm = false;
        $this->existingPatientIdForConfirm = null;
        $this->existingPatientDisplayName = '';
    }

    private function openExistingPatientConfirm(Patient $patient): void
    {
        $this->existingPatientIdForConfirm = $patient->id;
        $this->existingPatientDisplayName = $patient->displayName();
        $this->showExistingPatientConfirm = true;
    }

    public function saveNewDialog(): void
    {
        if ($this->newAge === '') {
            $this->newAge = null;
        }

        $this->validate([
            'newName' => ['required', 'string', 'max:120'],
            'newAge' => ['nullable', 'integer', 'min:0', 'max:120'],
            'newAddress' => ['nullable', 'string', 'max:255'],
            'newTelephone' => ['required', 'string', 'max:40'],
        ]);

        $telephone = trim($this->newTelephone);

        $existing = $this->patients()->findByTelephone($telephone);
        if ($existing !== null) {
            $this->openExistingPatientConfirm($existing);

            return;
        }

        try {
            $notes = trim(collect([
                $this->newAge !== null && $this->newAge !== '' ? 'Age: '.$this->newAge : null,
                $this->newAddress !== '' ? 'Address: '.$this->newAddress : null,
            ])->filter()->implode(PHP_EOL));

            $patient = $this->patients()->create([
                'first_name' => trim($this->newName),
                'last_name' => '',
                'telephone' => $telephone,
                'notes' => $notes !== '' ? $notes : null,
            ]);

            $this->appointments()->createTicket($patient->id);
            $this->closeNewDialog();
            session()->flash('status', __('Nouveau numéro enregistré.'));
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function setAppointmentStatus(int $id, string $to): void
    {
        $status = AppointmentStatus::tryFrom($to);
        if ($status === null) {
            return;
        }

        try {
            $this->appointments()->transitionStatus($id, $status);
            session()->flash('status', match ($status) {
                AppointmentStatus::Waiting => __('Remis en attente.'),
                AppointmentStatus::InProgress => __('Session démarrée.'),
                AppointmentStatus::Done => __('Session terminée.'),
                AppointmentStatus::Cancelled => __('Session annulée.'),
            });
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('appointment::queue-board-page', [
            'items' => $this->appointments()->listQueue(null),
            'estimateMinutes' => $this->prediction()->estimatedMinutesToClearQueue(),
        ])->title(__('Queue'));
    }
}
