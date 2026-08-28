<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): View
    {
        $usuarios = User::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('estado') && $request->input('estado') !== 'todos',
                fn ($query) => $query->where('estado', $request->input('estado')))
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'solicitante' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(10, ['*'], 'usuarios_page')
            ->withQueryString();

        $bitacora = AuditLog::query()
            ->when($request->filled('log_search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('log_search'));
                $query->where(function ($q) use ($search): void {
                    $q->where('actor_name', 'like', "%{$search}%")
                        ->orWhere('accion', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('log_modulo') && $request->input('log_modulo') !== 'todos',
                fn ($query) => $query->where('modulo', $request->input('log_modulo')))
            ->when($request->filled('log_nivel') && $request->input('log_nivel') !== 'todos',
                fn ($query) => $query->where('nivel', $request->input('log_nivel')))
            ->when($request->filled('log_fecha'),
                fn ($query) => $query->whereDate('created_at', $request->input('log_fecha')))
            ->latest('created_at')
            ->paginate(15, ['*'], 'bitacora_page')
            ->withQueryString();

        $modulos = AuditLog::query()
            ->select('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');

        return view('admin.usuarios', compact('usuarios', 'bitacora', 'modulos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:admin,solicitante'],
            'password' => ['nullable', 'string', 'min:8'],
            'temporary_password' => ['nullable', 'string', 'size:8', 'regex:/^[A-Z2-9]+$/'],
        ]);

        if ($datos['role'] === User::ROLE_ADMIN) {
            if (blank($datos['password'])) {
                return back()->withErrors(['password' => 'Escribe una contraseña para el administrador.'])->withInput();
            }
            $passwordInicial = $datos['password'];
            $esTemporal = false;
        } else {
            $passwordInicial = $datos['temporary_password'] ?? Str::password(8);
            $esTemporal = true;
        }

        $usuario = DB::transaction(function () use ($request, $datos, $passwordInicial, $esTemporal): User {
            $usuario = User::create([
                'name' => $datos['name'],
                'email' => strtolower(trim($datos['email'])),
                'telefono' => $datos['telefono'] ?? null,
                'role' => $datos['role'],
                'estado' => User::ESTADO_ACTIVO,
                'password' => Hash::make($passwordInicial),
                'debe_cambiar_password' => $esTemporal,
                'password_cambiado_at' => $esTemporal ? null : now(),
            ]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'crear_usuario',
                modulo: 'Usuarios',
                descripcion: "Creó la cuenta de {$usuario->name}.",
                nivel: AuditLogger::NIVEL_IMPORTANTE,
                sujeto: $usuario,
                valoresNuevos: [
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'telefono' => $usuario->telefono,
                    'role' => $usuario->role,
                    'estado' => $usuario->estado,
                ],
            );

            return $usuario;
        });

        $rol = $usuario->role === User::ROLE_ADMIN ? 'administrador' : 'solicitante';

        $mensaje = "Usuario {$rol} creado correctamente.";

        if ($esTemporal) {
            $mensaje .= " Contraseña temporal: {$passwordInicial}";
        }

        return redirect()
            ->route('admin.usuarios.index', ['tab' => 'usuarios'])
            ->with('success', $mensaje);
    }

    public function resetPassword(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Escribe la nueva contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        DB::transaction(function () use ($request, $usuario, $datos): void {
            $usuario->update([
                'password' => Hash::make($datos['password']),
                'debe_cambiar_password' => true,
                'password_cambiado_at' => null,
            ]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'restablecer_password_usuario',
                modulo: 'Usuarios',
                descripcion: "Restableció la contraseña de {$usuario->name}.",
                nivel: AuditLogger::NIVEL_IMPORTANTE,
                sujeto: $usuario,
                valoresNuevos: ['debe_cambiar_password' => true],
            );
        });

        return back()->with('success', "La contraseña de {$usuario->name} fue actualizada correctamente.");
    }

    public function cambiarEstado(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->withErrors(['usuario' => 'No puedes suspender tu propia cuenta.']);
        }

        $estadoAnterior = $usuario->estado;
        $nuevoEstado = $estadoAnterior === User::ESTADO_ACTIVO
            ? User::ESTADO_SUSPENDIDO
            : User::ESTADO_ACTIVO;

        DB::transaction(function () use ($request, $usuario, $estadoAnterior, $nuevoEstado): void {
            $usuario->update(['estado' => $nuevoEstado]);

            $this->auditLogger->registrar(
                request: $request,
                accion: 'cambiar_estado_usuario',
                modulo: 'Usuarios',
                descripcion: "Cambió el estado de {$usuario->name} de {$estadoAnterior} a {$nuevoEstado}.",
                nivel: AuditLogger::NIVEL_IMPORTANTE,
                sujeto: $usuario,
                valoresAnteriores: ['estado' => $estadoAnterior],
                valoresNuevos: ['estado' => $nuevoEstado],
            );
        });

        return back()->with('success', "El estado de {$usuario->name} cambió correctamente.");
    }
}
