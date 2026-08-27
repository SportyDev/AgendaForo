<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario Administrador
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // Correo para el login
            [
                'name' => 'Administrador del Sistema',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'estado' => User::ESTADO_ACTIVO,
            ]
        );

        // Usuario Solicitante
        User::updateOrCreate(
            ['email' => 'solicitante@gmail.com'], // Correo para el login
            [
                'name' => 'Usuario Solicitante',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SOLICITANTE,
                'estado' => User::ESTADO_ACTIVO,
            ]
        );
    }
}
