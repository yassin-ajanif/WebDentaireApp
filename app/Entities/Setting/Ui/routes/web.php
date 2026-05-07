<?php

use App\Entities\Setting\Ui\Livewire\QueueSettingsPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/settings/queue', QueueSettingsPage::class)->name('settings.queue');
});
