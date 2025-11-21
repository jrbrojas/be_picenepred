<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Obtener todos los roles
     *
     * Retorna la lista completa de los roles registrados en el sistema.
     */
    public function index()
    {
        $roles = Role::get();
        return response()->json([
            'list' => $roles
        ]);
    }
}
