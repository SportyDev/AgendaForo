<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InicioController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;

        return match ($role) {
            'admin' => redirect()->route('admin.solicitudes.index'),
            'solicitante' => redirect()->route('solicitante.reservas.historial'),
            default => abort(403),
        };
    }
}
