<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;

// ── Page d'accueil ─────────────────────────────────────────
Route::get('/', function () {
    return view('home');
})->name('home');

// ── Authentification (invités seulement) ──────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
});

// ── Déconnexion ────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── Pages protégées (utilisateur connecté) ─────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // CRUD Analyses
    Route::get('/analyze',         [AnalysisController::class, 'create'])->name('analysis.create');
    Route::post('/analyze',        [AnalysisController::class, 'store'])->name('analysis.store');
    Route::get('/analysis/{id}',   [AnalysisController::class, 'show'])->name('analysis.show');
    Route::get('/analysis/{id}/edit',   [AnalysisController::class, 'edit'])->name('analysis.edit');
    Route::put('/analysis/{id}',   [AnalysisController::class, 'update'])->name('analysis.update');
    Route::delete('/analysis/{id}',[AnalysisController::class, 'destroy'])->name('analysis.destroy');
});
