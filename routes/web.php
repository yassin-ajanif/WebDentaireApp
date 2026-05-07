<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/queue');

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['fr', 'ar'], true)) {
        abort(404);
    }
    session(['locale' => $locale]);

    return redirect()->back(fallback: route('queue.index'));
})->name('locale.switch');
