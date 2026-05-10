<?php

namespace App\Entities\Patient\Ui\Livewire;

use App\Entities\Patient\Contracts\PatientServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PatientFormPage extends Component
{
    public ?int $patientId = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $telephone = '';

    public string $notes = '';

    public function mount(?int $patientId = null): void
    {
        $this->patientId = $patientId;

        if ($patientId !== null) {
            $row = $this->patients()->find($patientId);
            if ($row === null) {
                abort(404);
            }
            $this->first_name = $row->first_name;
            $this->last_name = $row->last_name;
            $this->telephone = $row->telephone;
            $this->notes = (string) ($row->notes ?? '');
        }
    }

    public function save(): void
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'telephone' => ['required', 'string', 'max:40', 'regex:/^0[1-9]\d{8}$/'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($this->patientId === null) {
            $this->patients()->create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'telephone' => $this->telephone,
                'notes' => $this->notes,
            ]);
            session()->flash('status', __('Patient created.'));
        } else {
            $this->patients()->update($this->patientId, [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'telephone' => $this->telephone,
                'notes' => $this->notes,
            ]);
            session()->flash('status', __('Patient updated.'));
        }

        $this->redirectRoute('patients.index');
    }

    public function render()
    {
        return view('patient::patient-form-page')->title(
            $this->patientId === null ? __('New patient') : __('Edit patient')
        );
    }

    private function patients(): PatientServiceInterface
    {
        return app(PatientServiceInterface::class);
    }
}
