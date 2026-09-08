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

// Panel del cliente (placeholder — Fase 3)
Route::middleware('auth')->group(function () {
    Route::get('/jugadas', fn () => view('jugadas.index'))->name('jugadas.index');
});

// Backoffice del administrador (placeholder — Fase 6)
Route::middleware(['auth', 'auth.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');
});
