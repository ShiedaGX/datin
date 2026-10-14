<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataWilayahController;
use App\Http\Controllers\InputDataController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/input-data', [InputDataController::class, 'create'])->name('input-data.create');
    Route::post('/input-data', [InputDataController::class, 'store'])->name('input-data.store');

    Route::get('/data-wilayah', [DataWilayahController::class, 'index'])->name('data-wilayah.index');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
