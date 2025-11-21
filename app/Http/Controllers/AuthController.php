<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Registro de usuario
     *
     * Permite registrar un nuevo usuario en el sistema proporcionando los datos
     * requeridos.
     * Retorna el usuario creado junto con un mensaje de confirmación.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'email'     => 'required|string|email|unique:users',
            'password' => 'required|string|min:5',
            'password_confirmation' => 'required|required_with:password|same:password',
        ]);

        $data['rol'] = 'USUARIO';
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
     *
     * Autentica a un usuario existente verificando sus credenciales.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $request->authenticate();
        $user = auth('api')->user();

        // Verificar si el usuario está activo
        if (!$user || !$user->activo) {
            return response()->json(['message' => 'Usuario inactivo'], 403);
        }

        return response()->json([
            'status'       => 'success',
            // @var User
            'user'         => $user,
            'estado'         => $user->activo,
            'token' => $token,
        ], 200);
    }

    /**
     * Usuario autenticado (perfil)
     *
     * Obtiene la información del usuario autenticado actualmente, según el
     * token enviado en el encabezado `Authorization: Bearer.`
     */
    public function me(Request $request)
    {
        $user = auth('api')->user();
        return response()->json([
            // @var User
            'user'   => $user,
            'rol'    => $user->rol,
            'activo' => $user->activo,
        ]);
    }

    /**
     * Cerrar sesión
     *
     * Invalida el token actual, cerrando la sesión del usuario autenticado.
     */
    public function logout(Request $request)
    {
        auth('api')->logout(true);

        return response()->json(['message' => 'Sesion cerrada correctamente']);
    }

    /**
     * Refrescar el token
     *
     * Este token se refresca en el middleware de JWT
     */
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
            // @var User
            'user'         => $u,
            'rol'          => $u?->rol,
            'estado'       => $u?->activo,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // segundos
        ], 200);
    }
}

