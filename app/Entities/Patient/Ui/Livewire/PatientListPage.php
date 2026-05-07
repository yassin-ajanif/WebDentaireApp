<?php

namespace App\Entities\Patient\Ui\Livewire;

use App\Entities\Patient\Contracts\PatientServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PatientListPage extends Component
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

    public function delete(int $id): void
    {
        $this->patients()->delete($id);
        $this->dispatch('patient-deleted');
    }

    public function render()
    {
        return view('patient::patient-list-page', [
            'rows' => $this->patients()->paginate($this->search !== '' ? $this->search : null),
        ])->title(__('Patients'));
    }
}
