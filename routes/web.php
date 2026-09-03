<?php

use App\Http\Controllers\Admin\CalendarioController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Solicitante\ReservaController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('inicio.index');
});


Route::get('/inicio', [InicioController::class, 'index'])
    ->middleware(['auth', 'active'])
    ->name('inicio.index');


Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Rutas del Solicitante
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])
    ->prefix('solicitante')
    ->name('solicitante.')
    ->group(function () {

        // Calendario del solicitante
        Route::get('/calendario', [ReservaController::class, 'calendario'])
            ->name('calendario.index');

        Route::get('/calendario/eventos', [ReservaController::class, 'getEventos'])
            ->name('calendario.eventos');

        // Compatibilidad con la URL anterior del calendario.
        Route::get('/reservas', [ReservaController::class, 'index'])
            ->name('reservas.index');

        Route::post('/reservas', [ReservaController::class, 'store'])
            ->name('reservas.store');

        Route::get('/reservas/historial', [ReservaController::class, 'historial'])
            ->name('reservas.historial');

        Route::put('/reservas/{reserva}', [ReservaController::class, 'update'])
            ->name('reservas.update');

        Route::delete('/reservas/{reserva}', [ReservaController::class, 'destroy'])
            ->name('reservas.destroy');
    });


/*
|--------------------------------------------------------------------------
| Rutas del Administrador
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Bandeja de solicitudes

        Route::get('/solicitudes', [SolicitudController::class, 'index'])
            ->name('solicitudes.index');

        Route::put('/solicitudes/{reserva}/aprobar', [SolicitudController::class, 'aprobar'])
            ->name('solicitudes.aprobar');

        Route::put('/solicitudes/{reserva}/rechazar', [SolicitudController::class, 'rechazar'])
            ->name('solicitudes.rechazar');


        // Calendario maestro

        Route::get('/calendario', [CalendarioController::class, 'index'])
            ->name('calendario.index');

        Route::get('/calendario/eventos', [CalendarioController::class, 'getEventos'])
            ->name('calendario.eventos');

        Route::post('/calendario/bloquear', [CalendarioController::class, 'bloquear'])
            ->name('calendario.bloquear');


        // Gestión de usuarios y bitácora

        Route::get('/usuarios', [UsuarioController::class, 'index'])
            ->name('usuarios.index');

        Route::post('/usuarios', [UsuarioController::class, 'store'])
            ->name('usuarios.store');

        Route::patch('/usuarios/{usuario}/password', [UsuarioController::class, 'resetPassword'])
            ->name('usuarios.password.reset');

        Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])
            ->name('usuarios.estado');
    });


/*
|--------------------------------------------------------------------------
| Perfil y Seguridad
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/perfil', [PerfilController::class, 'edit'])
        ->name('perfil.edit');

    Route::put('/perfil', [PerfilController::class, 'updateProfile'])
        ->name('perfil.update');

    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])
        ->name('perfil.password');
});


require __DIR__ . '/auth.php';
