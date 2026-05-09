<?php

namespace App\Entities\Patient\Ui\Livewire;

use App\Entities\Patient\Contracts\PatientServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PatientTrashPage extends Component
{
    use WithPagination;

    public string $search = '';

    private function patients(): PatientServiceInterface
    {
        return app(PatientServiceInterface::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function restore(int $id): void
    {
        $this->patients()->restore($id);
        session()->flash('status', __('Patient restored.'));
    }

    public function render()
    {
        return view('patient::patient-trash-page', [
            'rows' => $this->patients()->paginateTrashed(
                $this->search !== '' ? $this->search : null
            ),
        ])->title(__('Deleted patients'));
    }
}
