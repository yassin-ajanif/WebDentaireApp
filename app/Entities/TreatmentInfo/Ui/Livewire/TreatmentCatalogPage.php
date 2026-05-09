<?php

namespace App\Entities\TreatmentInfo\Ui\Livewire;

use App\Entities\TreatmentInfo\Contracts\TreatmentCatalogServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TreatmentCatalogPage extends Component
{
    public bool $showTreatmentForm = false;
    public ?int $editingTreatmentId = null;
    public string $treatmentName = '';
    public string $treatmentPrice = '';

    public bool $showActivityForm = false;
    public ?int $editingActivityId = null;
    public string $activityName = '';
    public string $activityTreatmentId = '';

    private function catalog(): TreatmentCatalogServiceInterface
    {
        return app(TreatmentCatalogServiceInterface::class);
    }

    public function openTreatmentForm(): void
    {
        $this->reset(['treatmentName', 'treatmentPrice', 'editingTreatmentId']);
        $this->showTreatmentForm = true;
    }

    public function editTreatment(int $id): void
    {
        $treatment = $this->catalog()->findTreatment($id);
        if (!$treatment) return;
        $this->editingTreatmentId = $id;
        $this->treatmentName = $treatment->name;
        $this->treatmentPrice = (string) ($treatment->price ?? '');
        $this->showTreatmentForm = true;
    }

    public function saveTreatment(): void
    {
        $this->validate([
            'treatmentName' => ['required', 'string', 'max:255'],
            'treatmentPrice' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($this->editingTreatmentId) {
            $this->catalog()->updateTreatment($this->editingTreatmentId, [
                'name' => $this->treatmentName,
                'price' => $this->treatmentPrice !== '' ? $this->treatmentPrice : null,
            ]);
        } else {
            $this->catalog()->createTreatment([
                'name' => $this->treatmentName,
                'price' => $this->treatmentPrice !== '' ? $this->treatmentPrice : null,
            ]);
        }

        $this->cancelTreatmentForm();
    }

    public function cancelTreatmentForm(): void
    {
        $this->reset(['showTreatmentForm', 'editingTreatmentId', 'treatmentName', 'treatmentPrice']);
    }

    public function deleteTreatment(int $id): void
    {
        $this->catalog()->deleteTreatment($id);
    }

    public function openActivityForm(): void
    {
        $this->reset(['activityName', 'activityTreatmentId', 'editingActivityId']);
        $this->showActivityForm = true;
    }

    public function editActivity(int $id): void
    {
        $activities = $this->catalog()->allActivities();
        $activity = $activities->firstWhere('id', $id);
        if (!$activity) return;
        $this->editingActivityId = $id;
        $this->activityName = $activity->activity_name;
        $this->activityTreatmentId = (string) $activity->treatment_catalog_id;
        $this->showActivityForm = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activityName' => ['required', 'string', 'max:255'],
            'activityTreatmentId' => ['required', 'exists:treatment_catalog,id'],
        ]);

        if ($this->editingActivityId) {
            $this->catalog()->updateActivity($this->editingActivityId, $this->activityName);
        } else {
            $this->catalog()->createActivity((int) $this->activityTreatmentId, $this->activityName);
        }

        $this->cancelActivityForm();
    }

    public function cancelActivityForm(): void
    {
        $this->reset(['showActivityForm', 'editingActivityId', 'activityName', 'activityTreatmentId']);
    }

    public function deleteActivity(int $id): void
    {
        $this->catalog()->deleteActivity($id);
    }

    public function render()
    {
        return view('treatment_info::treatment-catalog-page', [
            'treatments' => $this->catalog()->allTreatments(),
            'activities' => $this->catalog()->allActivities(),
        ])->title(__('Treatment catalog'));
    }
}
