<?php

use App\Entities\TreatmentInfo\Ui\Livewire\TreatmentCatalogPage;
use App\Entities\TreatmentInfo\Ui\Livewire\TreatmentLinesPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/patients/{patient}/treatments', TreatmentLinesPage::class)->name('treatments.index');
    Route::get('/settings/treatments/catalog', TreatmentCatalogPage::class)->name('settings.treatments.catalog');
});
