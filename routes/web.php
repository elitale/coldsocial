<?php

use App\Http\Controllers\PersonaController;
use App\Http\Middleware\EnsurePersonaIsComplete;
use App\Http\Controllers\UpdateController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified', EnsurePersonaIsComplete::class])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('onboarding', [PersonaController::class, 'edit'])->name('onboarding.edit');
    Route::patch('onboarding', [PersonaController::class, 'update'])->name('onboarding.update');

    Route::get('updates', [UpdateController::class, 'index'])->name('updates.index');
    Route::post('updates', [UpdateController::class, 'store'])->name('updates.store');
    Route::delete('updates/{update}', [UpdateController::class, 'destroy'])->name('updates.destroy');
});

require __DIR__.'/settings.php';
