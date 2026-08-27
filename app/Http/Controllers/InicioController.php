<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InicioController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;

        return match ($role) {
            'admin' => view('dashboards.admin'),
            'solicitante' => view('dashboards.solicitante'),
            default => abort(403),
        };
    }
}
