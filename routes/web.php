<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UpdateController;
use App\Http\Middleware\EnsurePersonaIsComplete;
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

    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('posts/week', [PostController::class, 'week'])->name('posts.week');
    Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::post('posts/{post}/regenerate', [PostController::class, 'regenerate'])->name('posts.regenerate');
    Route::post('posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
    Route::post('posts/{post}/unapprove', [PostController::class, 'unapprove'])->name('posts.unapprove');
    Route::post('posts/{post}/schedule', [PostController::class, 'schedule'])->name('posts.schedule');
    Route::post('posts/{post}/unschedule', [PostController::class, 'unschedule'])->name('posts.unschedule');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::get('connections', [ConnectionController::class, 'index'])->name('connections.index');
    Route::get('connections/{platform}/redirect', [ConnectionController::class, 'redirect'])->name('connections.redirect');
    Route::get('connections/{platform}/callback', [ConnectionController::class, 'callback'])->name('connections.callback');
    Route::delete('connections/{platform}', [ConnectionController::class, 'destroy'])->name('connections.destroy');
});

require __DIR__.'/settings.php';
