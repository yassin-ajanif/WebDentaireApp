<?php

use App\Entities\Patient\Ui\Livewire\PatientFormPage;
use App\Entities\Patient\Ui\Livewire\PatientListPage;
use App\Entities\Patient\Ui\Livewire\PatientTrashPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/patients', PatientListPage::class)->name('patients.index');
    Route::get('/patients/trash', PatientTrashPage::class)->name('patients.trash');
    Route::get('/patients/create', PatientFormPage::class)->name('patients.create');
    Route::get('/patients/{patientId}/edit', PatientFormPage::class)->name('patients.edit');
});
