<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;

// ── Public ──
Route::view('/', 'home')->name('home');

// ── Authentification (Guests) ──
Route::middleware('guest')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/register', 'showRegister')->name('register');
        Route::post('/register', 'register');
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login');
    });
});

// ── Protected (Auth) ──
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1. Register the resource but EXCLUDE create and store to avoid the name conflict
    Route::resource('analysis', AnalysisController::class)->except(['index', 'create', 'store']);

    // 2. Manually define your custom /analyze paths with the names you want
    Route::get('/analyze', [AnalysisController::class, 'create'])->name('analysis.create');
    Route::post('/analyze', [AnalysisController::class, 'store'])->name('analysis.store');
});