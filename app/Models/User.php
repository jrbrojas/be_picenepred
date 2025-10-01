<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Campos que se pueden asignar en masa (mass assignment).
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'usuario',
        'email',
        'password',
        'rol',
        'fuente',
        'activo',
    ];

    /**
     * Campos que se ocultan cuando devuelves JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tipos de datos para casting automático.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
    ];
}
