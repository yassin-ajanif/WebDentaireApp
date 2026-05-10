<?php

namespace App\Entities\TreatmentInfo\Ui\Livewire;

use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
use App\Entities\Appointment\Enums\AppointmentStatus;
use App\Entities\Patient\Contracts\PatientServiceInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentCatalogServiceInterface;
use App\Entities\TreatmentInfo\Contracts\TreatmentInfoServiceInterface;
use App\Entities\TreatmentInfo\Enums\SessionStatus;
use App\Entities\TreatmentInfo\Enums\TreatmentStatus;
use App\Entities\TreatmentInfo\Models\Session;
use App\Entities\TreatmentInfo\Models\TreatmentInfo;
use DomainException;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TreatmentLinesPage extends Component
{
    public int $patient = 0;

    public ?int $activeAppointmentId = null;

    public bool $showTreatmentForm = false;

    public string $treatmentDescription = '';

    public string $globalPrice = '0.00';

    public string $correctionReason = '';

    public ?int $editingTreatmentId = null;

    public ?int $editingSessionId = null;

    public ?int $editingSessionTreatmentId = null;

    public ?int $activeSessionFormTreatmentId = null;

    public string $sessionCorrectionReason = '';

    public ?string $highlightSessionDate = null;

    /**
     * @var array<int, array{session_date:string, received_payment:string, notes:string}>
     */
    public array $sessionForms = [];

    /** @var array<int, bool> */
    public array $expandedTreatments = [];

    /** @var array<int, bool> */
    public array $expandedHistorySections = [];

    public function mount(int $patient): void
    {
        $this->patient = $patient;
        if ($this->patients()->find($patient) === null) {
            abort(404);
        }

        $appointmentId = (int) request()->query('appointment', 0);
        if ($appointmentId > 0) {
            $appointment = $this->appointments()->find($appointmentId);
            if (
                $appointment !== null
                && $appointment->patient_id === $this->patient
                && $appointment->status === AppointmentStatus::InProgress
            ) {
                $this->activeAppointmentId = $appointmentId;

                $treatments = $this->treatments()->listForPatient($this->patient);
                $latest = $treatments->first();
                if ($latest instanceof TreatmentInfo) {
                    $this->expandedTreatments[$latest->id] = true;
                }
            }
        }

        $selectedTreatmentId = (int) request()->query('treatment', 0);
        if ($selectedTreatmentId > 0) {
            $selectedTreatment = $this->treatments()
                ->listForPatient($this->patient)
                ->firstWhere('id', $selectedTreatmentId);

            if ($selectedTreatment instanceof TreatmentInfo) {
                $this->expandedTreatments[$selectedTreatmentId] = true;
            }
        }

        $highlightDate = (string) request()->query('highlight_date', '');
        if ($highlightDate !== '') {
            try {
                $this->highlightSessionDate = Carbon::parse($highlightDate)->toDateString();
            } catch (\Throwable) {
                $this->highlightSessionDate = null;
            }
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
        $this->correctionReason = '';
    }

    public function openTreatmentForm(): void
    {
        $this->showTreatmentForm = true;
    }

    public function cancelTreatmentEdit(): void
    {
        $this->editingTreatmentId = null;
        $this->showTreatmentForm = false;
        $this->reset(['treatmentDescription', 'globalPrice', 'correctionReason']);
        $this->globalPrice = '0.00';
    }

    public function saveTreatment(): void
    {
        $this->validate([
            'treatmentDescription' => ['required', 'string', 'max:500'],
            'globalPrice' => ['required', 'numeric', 'min:0'],
            'correctionReason' => $this->editingTreatmentId === null ? ['nullable', 'string', 'max:2000'] : ['required', 'string', 'max:2000'],
        ]);

        try {
            if ($this->editingTreatmentId === null) {
                $this->treatments()->createTreatment($this->patient, [
                    'description' => $this->treatmentDescription,
                    'global_price' => $this->globalPrice,
                ]);
                session()->flash('status', __('Treatment added.'));
            } else {
                $this->treatments()->createCorrection($this->editingTreatmentId, [
                    'description' => $this->treatmentDescription,
                    'global_price' => $this->globalPrice,
                    'reason' => $this->correctionReason,
                ], auth()->id());
                session()->flash('status', __('Treatment correction saved.'));
            }
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->cancelTreatmentEdit();
    }

    public function cancelTreatment(int $id): void
    {
        $this->treatments()->cancelTreatment($id);
        session()->flash('status', __('Treatment cancelled.'));
    }

    public function startEditSession(int $treatmentId, int $sessionId): void
    {
        $session = $this->findSession($treatmentId, $sessionId);
        if ($session === null) {
            return;
        }

        if ($session->status === SessionStatus::Cancelled->value) {
            session()->flash('error', __('Cannot edit a cancelled session.'));

            return;
        }

        $this->expandedTreatments[$treatmentId] = true;
        $this->activeSessionFormTreatmentId = $treatmentId;
        $this->editingSessionId = $sessionId;
        $this->editingSessionTreatmentId = $treatmentId;
        $this->sessionForms[$treatmentId] = [
            'session_date' => $session->session_date?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'),
            'received_payment' => (string) $session->received_payment,
            'notes' => (string) ($session->notes ?? ''),
        ];
        $this->sessionCorrectionReason = '';
    }

    public function openSessionForm(int $treatmentId): void
    {
        $this->expandedTreatments[$treatmentId] = true;
        $this->activeSessionFormTreatmentId = $treatmentId;
        $this->sessionForms[$treatmentId] ??= [
            'session_date' => now()->format('Y-m-d\TH:i'),
            'received_payment' => '0.00',
            'notes' => '',
        ];
    }

    public function toggleTreatmentExpanded(int $treatmentId): void
    {
        $current = $this->expandedTreatments[$treatmentId] ?? false;
        $next = ! $current;
        $this->expandedTreatments[$treatmentId] = $next;

        if (! $next && $this->activeSessionFormTreatmentId === $treatmentId) {
            $this->cancelSessionEdit($treatmentId);
        }
    }

    public function toggleHistorySection(int $treatmentId): void
    {
        $current = $this->expandedHistorySections[$treatmentId] ?? true;
        $this->expandedHistorySections[$treatmentId] = ! $current;
    }

    public function cancelSessionEdit(int $treatmentId): void
    {
        $this->editingSessionId = null;
        $this->editingSessionTreatmentId = null;
        $this->sessionCorrectionReason = '';
        if ($this->activeSessionFormTreatmentId === $treatmentId) {
            $this->activeSessionFormTreatmentId = null;
        }
        unset($this->sessionForms[$treatmentId]);
    }

    public function saveSession(int $treatmentId): void
    {
        $this->validate([
            "sessionForms.$treatmentId.session_date" => ['nullable', 'date'],
            "sessionForms.$treatmentId.received_payment" => ['required', 'numeric', 'min:0'],
            "sessionForms.$treatmentId.notes" => ['required', 'string', 'max:2000'],
        ]);

        $validated = $this->sessionForms[$treatmentId];

        if ($this->editingSessionId === null || $this->editingSessionTreatmentId !== $treatmentId) {
            $validated['session_date'] = now();
        }

        if ($this->editingSessionId !== null && $this->editingSessionTreatmentId === $treatmentId) {
            $this->validate([
                'sessionCorrectionReason' => ['required', 'string', 'max:2000'],
            ]);
        }

        try {
            if ($this->editingSessionId !== null && $this->editingSessionTreatmentId === $treatmentId) {
                $this->treatments()->updateSession($this->editingSessionId, [
                    ...$validated,
                    'reason' => $this->sessionCorrectionReason,
                    'created_by' => auth()->id(),
                ]);
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

    public function finishAppointment(): void
    {
        if ($this->activeAppointmentId === null) {
            return;
        }

        $appointment = $this->appointments()->find($this->activeAppointmentId);
        if (
            $appointment === null
            || $appointment->patient_id !== $this->patient
            || $appointment->status !== AppointmentStatus::InProgress
        ) {
            session()->flash('error', __('Appointment cannot be completed from this page.'));
            $this->activeAppointmentId = null;

            return;
        }

        try {
            $this->appointments()->transitionStatus($appointment->id, AppointmentStatus::Done);
            session()->flash('status', __('Session terminée.'));
            $this->activeAppointmentId = null;
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->redirect(route('queue.index'));
    }

    public function cancelSession(int $sessionId): void
    {
        try {
            $this->treatments()->cancelSession($sessionId);
            session()->flash('status', __('Session payment cancelled.'));
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $patientModel = $this->patients()->find($this->patient);
        $treatments = $this->treatments()->listForPatient($this->patient);
        $activeTreatments = $treatments->filter(fn (TreatmentInfo $t) => $t->status !== TreatmentStatus::Cancelled);
        $totalRemaining = $activeTreatments->sum(fn (TreatmentInfo $treatment): float => (float) $treatment->remaining_amount);
        $totalGlobal = $activeTreatments->sum(fn (TreatmentInfo $treatment): float => (float) $treatment->global_price);
        $totalPaid = max(0, $totalGlobal - $totalRemaining);

        $appointment = $this->activeAppointmentId !== null
            ? $this->appointments()->find($this->activeAppointmentId)
            : null;

        $amountExceedsRemaining = [];
        foreach ($this->sessionForms as $tid => $form) {
            $treatment = $activeTreatments->firstWhere('id', $tid);
            if ($treatment !== null) {
                $entered = (float) ($form['received_payment'] ?? 0);
                $maxAllowed = (float) $treatment->remaining_amount;
                $amountExceedsRemaining[$tid] = $entered > $maxAllowed;
            }
        }

        $hasSessions = $appointment !== null
            && $appointment->started_at !== null
            && $activeTreatments->contains(
                fn (TreatmentInfo $t) => $t->sessions->contains(
                    fn (Session $s) => $s->status !== SessionStatus::Cancelled->value
                        && $s->created_at !== null
                        && $s->created_at >= $appointment->started_at
                )
            );

        return view('treatment_info::treatment-lines-page', [
            'patientModel' => $patientModel,
            'treatments' => $treatments,
            'treatmentsCount' => $treatments->count(),
            'totalPaidAmount' => number_format($totalPaid, 2, '.', ''),
            'totalRemainingAmount' => number_format($totalRemaining, 2, '.', ''),
            'showFinishAppointmentButton' => $this->activeAppointmentId !== null,
            'hasSessions' => $hasSessions,
            'highlightSessionDate' => $this->highlightSessionDate,
            'treatmentCatalog' => $this->catalog()->getCatalog(),
            'amountExceedsRemaining' => $amountExceedsRemaining,
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

    private function catalog(): TreatmentCatalogServiceInterface
    {
        return app(TreatmentCatalogServiceInterface::class);
    }

    private function treatments(): TreatmentInfoServiceInterface
    {
        return app(TreatmentInfoServiceInterface::class);
    }

    private function patients(): PatientServiceInterface
    {
        return app(PatientServiceInterface::class);
    }

    private function appointments(): AppointmentServiceInterface
    {
        return app(AppointmentServiceInterface::class);
    }
}
