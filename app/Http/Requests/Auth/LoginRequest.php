<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intenta autenticar con JWT y devuelve el token.
     * Si falla, lanza un error 401 con mensaje claro.
     */
    public function authenticate(): string
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');

        // Autenticación usando el guard JWT
        $token = auth('api')->attempt($credentials);

        if (! $token) {
            RateLimiter::hit($this->throttleKey());

            // Respuesta controlada 401
            throw new HttpResponseException(
                response()->json([
                    'message' => 'Credenciales incorrectas.',
                ], 401)
            );
        }

        RateLimiter::clear($this->throttleKey());

        return $token;
    }

    /**
     * Verifica que no haya exceso de intentos fallidos.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw new HttpResponseException(
            response()->json([
                'message' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ], 429)
        );
    }

    /**
     * Genera la clave para el limitador de intentos por IP y email.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower((string) $this->input('email')) . '|' . $this->ip()
        );
    }
}
