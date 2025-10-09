<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios
     * - ADMIN y USER pueden ver la lista
     */
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    /**
     * Mostrar un usuario por ID
     * - ADMIN y USER pueden consultar
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        return response()->json($user, 200);
    }

    /**
     * Crear usuario
     * - Solo ADMIN puede crear
     */
    public function store(Request $request)
    {
        if (auth()->user()->rol !== 'ADMIN') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:6',
            'rol'       => 'required|in:ADMIN,USER',
        ]);

        $user = User::create([
            'nombres'   => $request->nombres,
            'apellidos' => $request->apellidos,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'rol'       => $request->rol,
            'fuente'    => $request->fuente,
            'activo'    => $request->activo ?? 1,
        ]);

        return response()->json(['message' => 'Usuario creado correctamente', 'user' => $user], 201);
    }

    /**
     * Actualizar usuario
     * - Solo ADMIN puede editar
     */
    public function update(Request $request, $id)
    {
        if (auth()->user()->rol !== 'ADMIN') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $request->validate([
            'email'    => 'email|unique:users,email,' . $id,
            'rol'      => 'in:ADMIN,USER',
            'password' => 'nullable|string|min:6',
        ]);

        $user->update([
            'nombres'   => $request->nombres ?? $user->nombres,
            'apellidos' => $request->apellidos ?? $user->apellidos,
            'email'     => $request->email ?? $user->email,
            'password'  => $request->password ? Hash::make($request->password) : $user->password,
            'rol'       => $request->rol ?? $user->rol,
            'fuente'    => $request->fuente ?? $user->fuente,
            'activo'    => $request->activo ?? $user->activo,
        ]);

        return response()->json(['message' => 'Usuario actualizado correctamente', 'user' => $user], 200);
    }

    /**
     * Eliminar usuario
     * - Solo ADMIN puede eliminar
     */
    public function destroy($id)
    {
        if (auth()->user()->rol !== 'ADMIN') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
    }
}
