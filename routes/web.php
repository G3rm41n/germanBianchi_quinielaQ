<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JugadaAdminController;
use App\Http\Controllers\Admin\PrecioController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BilleteraController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JugadaController;
use App\Http\Middleware\AdminMiddleware;
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
    Route::get('/jugadas', [JugadaController::class, 'index'])->name('jugadas.index');

    // Formulario de nueva jugada
    Route::get('/jugadas/crear', [JugadaController::class, 'create'])->name('jugadas.create');

    // Procesar nueva jugada
    Route::post('/jugadas', [JugadaController::class, 'store'])->name('jugadas.store');

    // Descargar PDF (stub — Fase 4)
    Route::get('/jugadas/{jugada}/pdf', [JugadaController::class, 'downloadPdf'])->name('jugadas.pdf');

    // Billetera y recarga de saldo (Fase 8)
    Route::post('/billetera/recargar', [BilleteraController::class, 'recargar'])->name('billetera.recargar');
});

Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::patch('/jugadas/{jugada}/estado', [JugadaAdminController::class, 'update'])->name('admin.jugadas.estado');
    Route::patch('/jugadas/{jugada}/resultado', [JugadaAdminController::class, 'actualizarResultado'])->name('admin.jugadas.resultado');
    Route::post('/jugadas/{jugada}/reenviar', [JugadaAdminController::class, 'reenviarNotificacion'])->name('admin.jugadas.reenviar');
    Route::delete('/limpiar', [JugadaAdminController::class, 'limpiarBaseDatos'])->name('admin.limpiar');

    Route::put('/precios/{tipo_juego}', [PrecioController::class, 'update'])->name('admin.precios.update');

    // Gestión de usuarios
    Route::get('/usuarios', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('admin.users.show');
});
