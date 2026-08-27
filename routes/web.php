<?php

use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// -- Panel de Administración --
Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->group(function () {
    // Aquí irán las rutas del administrador
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
});

// -- Panel de Solicitante --
Route::middleware(['auth', 'active'])->group(function () {
    // Aquí irán las rutas de solicitantes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

require __DIR__ . '/auth.php'; // En caso de usar Breeze/Jetstream/Laravel UI