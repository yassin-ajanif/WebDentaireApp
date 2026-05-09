<?php

use App\Entities\Chronology\Ui\Livewire\AppointmentTimelinePage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/queue/timeline', AppointmentTimelinePage::class)->name('queue.timeline');
});
