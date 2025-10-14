<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\HabitsIndex;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/', HabitsIndex::class)->name('habits.index');
});

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en','ja','fr']), 400);
    Session::put('locale', $locale);   // stocke dans la session
    return Redirect::back();
})->name('lang.switch');

require __DIR__.'/auth.php';
