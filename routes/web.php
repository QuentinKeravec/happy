<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\HabitsIndex;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Livewire\Admin\Dashboard;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/', HabitsIndex::class)->name('habits.index');
});

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en','ja','fr']), 400);
    Session::put('locale', $locale);   // stocke dans la session
    return Redirect::back();
})->name('lang.switch');

Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
});

require __DIR__.'/auth.php';
