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
        $data = $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string|min:6',
           // 'fuente'  => 'required|string|min:2',
            'rol'       => 'required|in:ADMIN,USER',
        ]);
        $data['activo'] = true;

        $user = User::create($data);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
            'rol'     => $user->rol,
            'activo'  => $user->activo,
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
        auth('api')->logout(true);
        // $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesion cerrada correctamente']);
    }

    public function refresh(): JsonResponse
    {
        $newToken = auth('api')->refresh();
        return $this->respondWithToken($newToken);
    }

    /**
     * Helper de respuesta estándar de JWT
     */
    protected function respondWithToken(string $token, ?User $user = null): JsonResponse
    {
        $u = $user ?? auth('api')->user();

        return response()->json([
            'status'       => 'success',
            'user'         => $u,
            'rol'          => $u?->rol,
            'estado'       => $u?->activo,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // segundos
        ], 200);
    }
}

