<?php

use App\Http\Controllers\InicioController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Solicitante\ReservaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inicio.index');
});

Route::get('/inicio', [InicioController::class, 'index'])
    ->middleware(['auth', 'active'])
    ->name('inicio.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'edit'])
        ->name('perfil.edit');

    Route::put('/perfil', [PerfilController::class, 'updateProfile'])
        ->name('perfil.update');

    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])
        ->name('perfil.password.update');
});

Route::middleware(['auth', 'active'])
    ->prefix('solicitante')
    ->name('solicitante.')
    ->group(function () {
        Route::get('/reservas', [ReservaController::class, 'index'])
            ->name('reservas.index');

        Route::get('/reservas/eventos', [ReservaController::class, 'getEventos'])
            ->name('reservas.eventos');

        Route::post('/reservas', [ReservaController::class, 'store'])
            ->name('reservas.store');

        Route::get('/reservas/historial', [ReservaController::class, 'historial'])
            ->name('reservas.historial');

        Route::put('/reservas/{reserva}', [ReservaController::class, 'update'])
            ->name('reservas.update');

        Route::delete('/reservas/{reserva}', [ReservaController::class, 'destroy'])
            ->name('reservas.destroy');
    });

require __DIR__ . '/auth.php';
