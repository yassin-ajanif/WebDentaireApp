<?php

namespace App\Entities\TreatmentInfo\Ui\Livewire;

use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use DomainException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TreatmentLinesPage extends Component
{
    public int $patient = 0;

    public string $description = '';

    public int $quantity = 1;

    public string $unit_price = '0';

    public ?int $editingId = null;

    public function mount(int $patient): void
    {
        $this->patient = $patient;
        if ($this->patients()->find($patient) === null) {
            abort(404);
        }
    }

    public function startEdit(int $id): void
    {
        $rows = $this->treatments()->listForPatient($this->patient);
        $row = $rows->firstWhere('id', $id);
        if ($row === null) {
            return;
        }
        $this->editingId = $id;
        $this->description = $row->description;
        $this->quantity = $row->quantity;
        $this->unit_price = (string) $row->unit_price;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->reset(['description', 'quantity', 'unit_price']);
        $this->quantity = 1;
        $this->unit_price = '0';
    }

    public function saveLine(): void
    {
        $this->validate([
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            if ($this->editingId === null) {
                $this->treatments()->create($this->patient, [
                    'description' => $this->description,
                    'quantity' => $this->quantity,
                    'unit_price' => $this->unit_price,
                ]);
                session()->flash('status', __('Line added.'));
            } else {
                $this->treatments()->update($this->editingId, [
                    'description' => $this->description,
                    'quantity' => $this->quantity,
                    'unit_price' => $this->unit_price,
                ]);
                session()->flash('status', __('Line updated.'));
            }
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->cancelEdit();
    }

    public function deleteLine(int $id): void
    {
        $this->treatments()->delete($id);
        session()->flash('status', __('Line removed.'));
    }

    public function render()
    {
        $patientModel = $this->patients()->find($this->patient);

        return view('treatment_info::treatment-lines-page', [
            'patientModel' => $patientModel,
            'lines' => $this->treatments()->listForPatient($this->patient),
        ])->title(__('Treatments'));
    }

    private function treatments(): TreatmentInfoServiceInterface
    {
        return app(TreatmentInfoServiceInterface::class);
    }

    private function patients(): PatientServiceInterface
    {
        return app(PatientServiceInterface::class);
    }
}
