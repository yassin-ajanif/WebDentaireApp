<?php

use App\Entities\Patient\Ui\Livewire\PatientFormPage;
use App\Entities\Patient\Ui\Livewire\PatientListPage;
use App\Entities\Patient\Ui\Livewire\PatientTrashPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/patients', PatientListPage::class)->name('patients.index')->middleware('auth');
    Route::get('/patients/trash', PatientTrashPage::class)->name('patients.trash')->middleware('auth');
    Route::get('/patients/create', PatientFormPage::class)->name('patients.create')->middleware('auth');
    Route::get('/patients/{patientId}/edit', PatientFormPage::class)->name('patients.edit')->middleware('auth');
});
