<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        User::create([
            'nombres'   => 'MARIA',
            'apellidos' => 'SANTIAGO RUIZ',
            'email'     => 'msantiago@cenepred.gob.pe',
            'password'  => '123456',
            'rol'       => 'ADMIN',
          //  'fuente'    => null,
            'activo'    => 1,
        ]);

        // USER
        User::create([
            'nombres'   => 'JUAN',
            'apellidos' => 'PEREZ LOPEZ',
            'email'     => 'jperez@cenepred.gob.pe',
            'password'  => '123456',
            'rol'       => 'USUARIO',
          //  'fuente'    => 'SIGRID',
            'activo'    => 1,
        ]);

        User::create([
            'nombres'   => 'Super',
            'apellidos' => 'Admin',
            'email'     => 'admin@cenepred.gob.pe',
            'password'  => '$Cenepred2025$',
            'rol'       => 'ADMIN',
          //  'fuente'    => 'SIGRID',
            'activo'    => 1,
        ]);

        User::create([
            'nombres'   => 'Usuario',
            'apellidos' => 'Secundario',
            'email'     => 'usuario@cenepred.gob.pe',
            'password'  => '$Cenepred2025$',
            'rol'       => 'USUARIO',
          //  'fuente'    => 'SIGRID',
            'activo'    => 1,
        ]);
    }
}

