<?php

use App\Http\Controllers\Pdf\ReporteCobrosController;
use App\Http\Controllers\Pdf\ReportePagosController;
use App\Http\Controllers\Pdf\ReporteSuscripcionMovimiento;
use App\Http\Controllers\DirectorioController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\WelcomeController;

Route::get('/', WelcomeController::class);

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard-cliente');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Link de verificación enviado.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard-cliente', function () {
        return redirect()->route('cliente.dashboard');
    });

    Route::get('/pdf/contrato/{id}', [App\Http\Controllers\Pdf\ReporteContratoController::class, 'descargar'])
        ->name('pdf.contrato');

    Route::prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'dashboard'])
            ->name('dashboard');

        // Mi Tienda
        Route::get('/tienda', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'tienda'])
            ->name('tienda');
        Route::post('/tienda', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'actualizarTienda'])
            ->name('tienda.update');

        // Productos CRUD
        Route::get('/productos', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'productos'])
            ->name('productos.index');
        Route::get('/productos/crear', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'crearProducto'])
            ->name('productos.create');
        Route::post('/productos', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'guardarProducto'])
            ->name('productos.store');
        Route::get('/productos/{id}/editar', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'editarProducto'])
            ->name('productos.edit');
        Route::put('/productos/{id}', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'actualizarProducto'])
            ->name('productos.update');
        Route::delete('/productos/{id}', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'eliminarProducto'])
            ->name('productos.destroy');

        // Marcas CRUD
        Route::get('/marcas', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'marcas'])
            ->name('marcas.index');
        Route::get('/marcas/crear', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'crearMarca'])
            ->name('marcas.create');
        Route::post('/marcas', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'guardarMarca'])
            ->name('marcas.store');
        Route::get('/marcas/{id}/editar', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'editarMarca'])
            ->name('marcas.edit');
        Route::put('/marcas/{id}', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'actualizarMarca'])
            ->name('marcas.update');
        Route::delete('/marcas/{id}', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'eliminarMarca'])
            ->name('marcas.destroy');

        // Estado de Cuenta
        Route::get('/estado-cuenta', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'estadoCuenta'])
            ->name('estado-cuenta');
        Route::post('/estado-cuenta/reportar-pago', [App\Http\Controllers\Cliente\ClientDashboardController::class, 'registrarPago'])
            ->name('reportar-pago');
    });
});

Route::get('/cobros/pdf/{id}', [ReporteCobrosController::class, 'cobro'])
    ->name('cobros.pdf');

Route::get('/pdf/pago/{id}', [ReportePagosController::class, 'pago'])
    ->name('pdf.pago');

Route::get('/pdf/suscripcion/movimiento/{id}', [ReporteSuscripcionMovimiento::class, 'movimiento'])
    ->name('pdf.suscripcion.movimiento');

Route::get('/directorio', [DirectorioController::class, 'index'])->name('directorio.index');
Route::get('/directorio/{id}/catalogo', [DirectorioController::class, 'catalogo'])->name('directorio.catalogo');

Route::get('/suscripciones', function () {
    return view('suscripciones');
})->name('suscripciones');

Route::get('/productos', function () {
    return view('productos');
})->name('productos');
