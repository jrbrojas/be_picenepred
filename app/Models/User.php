<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Campos que se pueden asignar en masa (mass assignment).
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'email',
        'password',
        'rol',
        // 'fuente',
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
        'password' => 'hashed',
    ];


    public function scopeSearch(Builder $query, $value)
    {
        $query->where('nombres', 'ilike', "%{$value}%")
            ->orWhere('apellidos', 'ilike', "%{$value}%")
            ->orWhere('email', 'ilike', "%{$value}%");
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
