<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:lecturer')
        ->prefix('lecturer')
        ->name('lecturer.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'lecturer'])->name('dashboard');
        });

    Route::middleware('role:student')
        ->prefix('student')
        ->name('student.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');
        });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
