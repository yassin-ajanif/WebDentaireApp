<?php

use App\Entities\Report\Ui\Livewire\ReportsPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/reports', ReportsPage::class)->name('reports.index')->middleware('auth');
});
