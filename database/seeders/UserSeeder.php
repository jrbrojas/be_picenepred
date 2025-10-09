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
            'password'  => Hash::make('1234'),
            'rol'       => 'ADMIN',
            'fuente'    => null,
            'activo'    => 1,
        ]);

        // USER
        User::create([
            'nombres'   => 'JUAN',
            'apellidos' => 'PEREZ LOPEZ',
            'email'     => 'jperez@cenepred.gob.pe',
            'password'  => Hash::make('123456'),
            'rol'       => 'USER',
            'fuente'    => 'SIGRID',
            'activo'    => 1,
        ]);
    }
}

