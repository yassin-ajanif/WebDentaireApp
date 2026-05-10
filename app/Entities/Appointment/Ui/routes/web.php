<?php

use App\Entities\Appointment\Ui\Livewire\QueueBoardPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/queue', QueueBoardPage::class)->name('queue.index')->middleware('auth');
});
