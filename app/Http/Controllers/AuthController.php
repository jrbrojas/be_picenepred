<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{

    /**
     * Registro de usuario
     */
    public function register(Request $request)
    {
        $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'usuario'   => 'required|string|max:50|unique:users',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string|min:6',
            'rol'       => 'required|in:ADMIN,USER',
        ]);

        $user = User::create([
            'nombres'   => $request->nombres,
            'apellidos' => $request->apellidos,
            'usuario'   => $request->usuario,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'rol'       => $request->rol,
            'fuente'    => $request->fuente,
            'activo'    => 1, // por defecto activo
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
            'rol'     => $user->rol,
            'activo'  => $user->activo,
            'token'   => $token,
        ], 201);
    }

    /**
     * Login de usuario
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $request->authenticate();
        $user = auth('api')->user();

        // Verificar si el usuario está activo
        if (!$user->activo) {
            return response()->json(['message' => 'Usuario inactivo'], 403);
        }

        // $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status'       => 'success',
            'user'         => $user,
            'rol'         => $user->rol,
            'estado'         => $user->activo,
            'token' => $token,
        ], 200);
    }

    /**
     * Usuario autenticado (perfil)
     */
    public function me(Request $request)
    {
        $user = auth('api')->user();
        return response()->json([
            'user'   => $user,
            'rol'    => $user->rol,
            'activo' => $user->activo,
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}

