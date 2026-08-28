<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function edit(): View
    {
        return view('perfil.edit', [
            'usuario' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'telefono.max' => 'El teléfono no puede superar los 30 caracteres.',
        ]);

        $nombreAnterior = $usuario->name;
        $telefonoAnterior = $usuario->telefono;

        $usuario->forceFill([
            'name' => trim($datos['name']),
            'telefono' => isset($datos['telefono']) && trim($datos['telefono']) !== ''
                ? trim($datos['telefono'])
                : null,
        ])->save();

        $this->auditLogger->registrar(
            request: $request,
            accion: 'actualizar_perfil',
            modulo: 'Perfil',
            descripcion: "Actualizó su información personal de perfil.",
            nivel: AuditLogger::NIVEL_IMPORTANTE,
            sujeto: $usuario,
            valoresAnteriores: [
                'nombre' => $nombreAnterior,
                'telefono' => $telefonoAnterior,
            ],
            valoresNuevos: [
                'nombre' => $usuario->name,
                'telefono' => $usuario->telefono,
            ],
        );

        return back()->with('success', 'Tus datos personales se actualizaron correctamente.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Escribe tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Escribe una nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
        ]);

        $usuario = $request->user();

        $usuario->forceFill([
            'password' => Hash::make($datos['password']),
            'password_cambiado_at' => now(),
        ])->save();

        $this->auditLogger->registrar(
            request: $request,
            accion: 'cambiar_password_propio',
            modulo: 'Perfil',
            descripcion: 'Actualizó su propia contraseña de acceso.',
            nivel: AuditLogger::NIVEL_IMPORTANTE,
            sujeto: $usuario,
        );

        return back()->with('success', 'Tu contraseña se actualizó correctamente.');
    }
}
