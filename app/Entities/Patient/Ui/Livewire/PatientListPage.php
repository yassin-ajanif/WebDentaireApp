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
    public string $paymentFilter = 'all';

    private function patients(): PatientServiceInterface
    {
        return app(PatientServiceInterface::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setPaymentFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'paid', 'unpaid'], true)) {
            return;
        }

        $this->paymentFilter = $filter;
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->patients()->delete($id);
        $this->dispatch('patient-deleted');
    }

    public function render()
    {
        $rows = $this->patients()->paginate(
            $this->search !== '' ? $this->search : null,
            $this->paymentFilter
        );

        $owedMap = $this->patients()->owedAmountsForPatients($rows->pluck('id')->toArray());

        return view('patient::patient-list-page', [
            'rows' => $rows,
            'owedMap' => $owedMap,
        ])->title(__('Patients'));
    }
}
