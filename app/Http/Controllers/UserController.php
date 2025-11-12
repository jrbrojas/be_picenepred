<?php

namespace App\Http\Controllers;

use App\Models\Escenario;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $usuarios = User::search($request['query'])->orderBy('apellidos')->get();
        // eviar los parametros de esta forma para que el datatable del front los pueda leer sin problemas
        return response()->json([
            'list' => $usuarios,
            'total' => $usuarios->count(),
        ]);
    }

    /**
     * Mostrar un usuario por ID
     * - ADMIN y USER pueden consultar
     */
    public function show(User $usuario)
    {
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        /** @var User */
        return response()->json($usuario, 200);
    }

    /**
     * Crear usuario
     * - Solo ADMIN puede crear
     */
    public function store(Request $request)
    {
        // creacion de prueba
        $data = $request->validate([
            'rol'      => 'required|in:ADMIN,USER',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'sometimes|string|min:5',
            'password_confirmation' => 'sometimes|required_with:password|same:password',
        ]);

        User::create($data);

        return response()->json([
            'message' => 'Usuario credo exitoasamente!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function update(Request $request, User $usuario)
    {
        if (auth()->user()->rol !== 'ADMIN') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $data = $request->validate([
            'nombres'  => 'nullable|string|max:100',
            'apellidos'  => 'nullable|string|max:100',
            'email'    => 'email|unique:users,email,' . $usuario->id,
            'rol'      => 'required|in:ADMIN,USER',
            'password' => 'nullable|string|min:6',
            'fuente'  => 'nullable|string|max:100',
            'activo'  => 'nullable|string|max:100',
        ]);

        $usuario->update($data);

        /** @var array{'message': string, user: User } */
        return response()->json(['message' => 'Usuario actualizado correctamente', 'user' => $usuario], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario)
    {
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
