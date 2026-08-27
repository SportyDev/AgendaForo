<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $passwordTemporal = Str::random(10);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($passwordTemporal),
            'role' => User::ROLE_SOLICITANTE,
            'estado' => User::ESTADO_ACTIVO,
        ]);

        $this->auditLogger->registrar(
            request: $request,
            accion: 'crear_usuario',
            modulo: 'Usuarios',
            descripcion: "Se creó el usuario solicitante: {$user->email}",
            sujeto: $user,
            valoresNuevos: $user->toArray()
        );

        // Retornar back con el password temporal u otra lógica según necesites
        return back()->with('success', 'Usuario creado. Contraseña temporal: ' . $passwordTemporal);
    }
}
