<?php

use App\Http\Controllers\PersonaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UpdateController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('onboarding', [PersonaController::class, 'edit'])->name('onboarding.edit');
    Route::patch('onboarding', [PersonaController::class, 'update'])->name('onboarding.update');

    Route::get('updates', [UpdateController::class, 'index'])->name('updates.index');
    Route::post('updates', [UpdateController::class, 'store'])->name('updates.store');
    Route::delete('updates/{update}', [UpdateController::class, 'destroy'])->name('updates.destroy');

    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

require __DIR__.'/settings.php';
