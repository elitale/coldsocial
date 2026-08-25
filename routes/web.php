<?php

use App\Http\Controllers\PersonaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('onboarding', [PersonaController::class, 'edit'])->name('onboarding.edit');
    Route::patch('onboarding', [PersonaController::class, 'update'])->name('onboarding.update');
});

require __DIR__.'/settings.php';
