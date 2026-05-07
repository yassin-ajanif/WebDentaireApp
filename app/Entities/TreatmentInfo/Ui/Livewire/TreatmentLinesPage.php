<?php

namespace App\Entities\TreatmentInfo\Ui\Livewire;

use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use App\Entities\TreatmentInfo\Models\Session;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use DomainException;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TreatmentLinesPage extends Component
{
    public int $patient = 0;

    public bool $showTreatmentForm = false;

    public string $treatmentDescription = '';

    public string $globalPrice = '0.00';

    public ?int $editingTreatmentId = null;

    public ?int $editingSessionId = null;

    public ?int $editingSessionTreatmentId = null;

    public ?int $activeSessionFormTreatmentId = null;

    /**
     * @var array<int, array{session_date:string, received_payment:string, notes:string}>
     */
    public array $sessionForms = [];

    public function mount(int $patient): void
    {
        $this->patient = $patient;
        if ($this->patients()->find($patient) === null) {
            abort(404);
        }
    }

    public function startEditTreatment(int $id): void
    {
        $treatment = $this->treatments()->listForPatient($this->patient)->firstWhere('id', $id);
        if (! $treatment instanceof TreatmentInfo) {
            return;
        }

        $this->showTreatmentForm = true;
        $this->editingTreatmentId = $id;
        $this->treatmentDescription = (string) $treatment->description;
        $this->globalPrice = (string) $treatment->global_price;
    }

    public function openTreatmentForm(): void
    {
        $this->showTreatmentForm = true;
    }

    public function cancelTreatmentEdit(): void
    {
        $this->editingTreatmentId = null;
        $this->showTreatmentForm = false;
        $this->reset(['treatmentDescription', 'globalPrice']);
        $this->globalPrice = '0.00';
    }

    public function saveTreatment(): void
    {
        $this->validate([
            'treatmentDescription' => ['required', 'string', 'max:500'],
            'globalPrice' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            if ($this->editingTreatmentId === null) {
                $this->treatments()->createTreatment($this->patient, [
                    'description' => $this->treatmentDescription,
                    'global_price' => $this->globalPrice,
                ]);
                session()->flash('status', __('Treatment added.'));
            } else {
                $this->treatments()->updateTreatment($this->editingTreatmentId, [
                    'description' => $this->treatmentDescription,
                    'global_price' => $this->globalPrice,
                ]);
                session()->flash('status', __('Treatment updated.'));
            }
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->cancelTreatmentEdit();
    }

    public function deleteTreatment(int $id): void
    {
        $this->treatments()->deleteTreatment($id);
        session()->flash('status', __('Treatment removed.'));
    }

    public function startEditSession(int $treatmentId, int $sessionId): void
    {
        $session = $this->findSession($treatmentId, $sessionId);
        if ($session === null) {
            return;
        }

        $this->activeSessionFormTreatmentId = $treatmentId;
        $this->editingSessionId = $sessionId;
        $this->editingSessionTreatmentId = $treatmentId;
        $this->sessionForms[$treatmentId] = [
            'session_date' => $session->session_date?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'),
            'received_payment' => (string) $session->received_payment,
            'notes' => (string) ($session->notes ?? ''),
        ];
    }

    public function openSessionForm(int $treatmentId): void
    {
        $this->activeSessionFormTreatmentId = $treatmentId;
        $this->sessionForms[$treatmentId] ??= [
            'session_date' => now()->format('Y-m-d\TH:i'),
            'received_payment' => '0.00',
            'notes' => '',
        ];
    }

    public function cancelSessionEdit(int $treatmentId): void
    {
        $this->editingSessionId = null;
        $this->editingSessionTreatmentId = null;
        if ($this->activeSessionFormTreatmentId === $treatmentId) {
            $this->activeSessionFormTreatmentId = null;
        }
        unset($this->sessionForms[$treatmentId]);
    }

    public function saveSession(int $treatmentId): void
    {
        $payload = $this->sessionForms[$treatmentId] ?? [];
        $validated = Validator::make($payload, [
            'session_date' => ['required', 'date'],
            'received_payment' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        try {
            if ($this->editingSessionId !== null && $this->editingSessionTreatmentId === $treatmentId) {
                $this->treatments()->updateSession($this->editingSessionId, $validated);
                session()->flash('status', __('Session payment updated.'));
            } else {
                $this->treatments()->createSession($treatmentId, $validated);
                session()->flash('status', __('Session payment added.'));
            }
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->cancelSessionEdit($treatmentId);
    }

    public function deleteSession(int $sessionId): void
    {
        $this->treatments()->deleteSession($sessionId);
        session()->flash('status', __('Session payment removed.'));
    }

    public function render()
    {
        $patientModel = $this->patients()->find($this->patient);
        $treatments = $this->treatments()->listForPatient($this->patient);
        $totalRemaining = $treatments->sum(fn (TreatmentInfo $treatment): float => (float) $treatment->remaining_amount);
        $totalGlobal = $treatments->sum(fn (TreatmentInfo $treatment): float => (float) $treatment->global_price);
        $totalPaid = max(0, $totalGlobal - $totalRemaining);

        return view('treatment_info::treatment-lines-page', [
            'patientModel' => $patientModel,
            'treatments' => $treatments,
            'treatmentsCount' => $treatments->count(),
            'totalPaidAmount' => number_format($totalPaid, 2, '.', ''),
            'totalRemainingAmount' => number_format($totalRemaining, 2, '.', ''),
        ])->title(__('Treatments'));
    }

    private function findSession(int $treatmentId, int $sessionId): ?Session
    {
        $treatment = $this->treatments()->listForPatient($this->patient)->firstWhere('id', $treatmentId);
        if (! $treatment instanceof TreatmentInfo) {
            return null;
        }

        $session = $treatment->sessions->firstWhere('id', $sessionId);

        return $session instanceof Session ? $session : null;
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
