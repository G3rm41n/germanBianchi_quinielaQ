<?php

use App\Http\Controllers\Admin\CierreCajaController;
use App\Http\Controllers\Admin\ClienteExcelController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JugadaAdminController;
use App\Http\Controllers\Admin\PrecioController;
use App\Http\Controllers\Admin\SorteoAdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BilleteraController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JugadaController;
use App\Http\Controllers\SorteoPublicController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Página de inicio pública
Route::get('/', [HomeController::class, 'index'])->name('home');

// Pizarra pública de resultados de sorteos oficiales
Route::get('/sorteos', [SorteoPublicController::class, 'index'])->name('sorteos.index');
Route::get('/sorteos/{sorteo}', [SorteoPublicController::class, 'show'])->name('sorteos.show');

// Rutas de autenticación — solo para invitados (no autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Recuperación de Contraseña
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/forgot-password/sent', [ForgotPasswordController::class, 'sent'])->name('password.sent');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

// Logout
Route::post('/logout', [LogoutController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// Panel del cliente y Verificación de Correo (Fase 3 & Auth)
Route::middleware('auth')->group(function () {
    // Verificación de Correo Electrónico
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
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
    Route::post('/jugadas/bulk', [JugadaAdminController::class, 'bulkUpdate'])->name('admin.jugadas.bulk');
    Route::post('/jugadas/bulk-hoy', [JugadaAdminController::class, 'bulkProcesarTodasHoy'])->name('admin.jugadas.bulk_hoy');
    Route::post('/jugadas/{jugada}/reenviar', [JugadaAdminController::class, 'reenviarNotificacion'])->name('admin.jugadas.reenviar');
    Route::delete('/limpiar', [JugadaAdminController::class, 'limpiarBaseDatos'])->name('admin.limpiar');

    // Cierre de Caja y Arqueo Diario
    Route::get('/caja', [CierreCajaController::class, 'index'])->name('admin.caja.index');
    Route::get('/caja/pdf', [CierreCajaController::class, 'exportarPdf'])->name('admin.caja.pdf');

    Route::put('/precios/{tipo_juego}', [PrecioController::class, 'update'])->name('admin.precios.update');

    // Gestión de usuarios y herramientas Excel
    Route::get('/usuarios/exportar', [ClienteExcelController::class, 'exportar'])->name('admin.users.exportar');
    Route::get('/usuarios/plantilla-ejemplo', [ClienteExcelController::class, 'descargarPlantilla'])->name('admin.users.plantilla');
    Route::post('/usuarios/importar', [ClienteExcelController::class, 'importar'])->name('admin.users.importar');
    Route::get('/usuarios', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('admin.users.show');

    // Motor de Sorteos Oficiales y Escrutinio Automático
    Route::get('/sorteos', [SorteoAdminController::class, 'index'])->name('admin.sorteos.index');
    Route::get('/sorteos/crear', [SorteoAdminController::class, 'create'])->name('admin.sorteos.create');
    Route::post('/sorteos', [SorteoAdminController::class, 'store'])->name('admin.sorteos.store');
    Route::get('/sorteos/{sorteo}', [SorteoAdminController::class, 'show'])->name('admin.sorteos.show');
    Route::post('/sorteos/{sorteo}/escrutar', [SorteoAdminController::class, 'escrutar'])->name('admin.sorteos.escrutar');
    Route::post('/sorteos/{sorteo}/revertir', [SorteoAdminController::class, 'revertir'])->name('admin.sorteos.revertir');
    Route::delete('/sorteos/{sorteo}', [SorteoAdminController::class, 'destroy'])->name('admin.sorteos.destroy');
});
