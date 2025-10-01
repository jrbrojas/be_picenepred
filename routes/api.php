<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Rutas públicas
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Rutas protegidas con Sanctum
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // CRUD usuarios
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::get('/usuarios/{id}', [UserController::class, 'show']);
        Route::put('/usuarios/{id}', [UserController::class, 'update']);
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    });
});

