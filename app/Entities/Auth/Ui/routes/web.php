<?php

use App\Entities\Auth\Ui\Livewire\LoginPage;
use App\Entities\Auth\Ui\Livewire\ResetPasswordPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', LoginPage::class)->name('login');
        Route::get('/reset-password', ResetPasswordPage::class)->name('auth.reset-password');
    });

    Route::post('/logout', function () {
        app(\App\Entities\Auth\Contracts\AuthServiceInterface::class)->logout();

        return redirect()->route('login');
    })->name('auth.logout')->middleware('auth');
});
