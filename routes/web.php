<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Página de inicio pública
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rutas de autenticación — solo para invitados (no autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// Logout
Route::post('/logout', [LogoutController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// Panel del cliente (Fase 3)
Route::middleware('auth')->group(function () {
    // Historial de jugadas del cliente
    Route::get('/jugadas', [\App\Http\Controllers\JugadaController::class, 'index'])->name('jugadas.index');

    // Formulario de nueva jugada
    Route::get('/jugadas/crear', [\App\Http\Controllers\JugadaController::class, 'create'])->name('jugadas.create');

    // Procesar nueva jugada
    Route::post('/jugadas', [\App\Http\Controllers\JugadaController::class, 'store'])->name('jugadas.store');

    // Descargar PDF (stub — Fase 4)
    Route::get('/jugadas/{jugada}/pdf', [\App\Http\Controllers\JugadaController::class, 'downloadPdf'])->name('jugadas.pdf');
});

// Backoffice del administrador (placeholder — Fase 6)
Route::middleware(['auth', 'auth.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');
});
