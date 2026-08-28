<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AuditLogger
{
    public const NIVEL_OPERATIVA = 'operativa';
    public const NIVEL_IMPORTANTE = 'importante';
    public const NIVEL_CRITICA = 'critica';

    public function registrar(
        Request $request,
        string $accion,
        string $modulo,
        string $descripcion,
        string $nivel = self::NIVEL_IMPORTANTE,
        $sujeto = null,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null
    ): void {
        $user = $request->user();

        DB::table('audit_logs')->insert([
            'actor_id' => $user?->id,
            'actor_name' => $user?->name ?? 'Sistema',
            'actor_role' => $user?->role ?? 'N/A',
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'nivel' => $nivel,
            'sujeto_type' => $sujeto ? get_class($sujeto) : null,
            'sujeto_id' => $sujeto ? $sujeto->id : null,
            'valores_anteriores' => $valoresAnteriores
                ? json_encode($valoresAnteriores)
                : null,
            'valores_nuevos' => $valoresNuevos
                ? json_encode($valoresNuevos)
                : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
