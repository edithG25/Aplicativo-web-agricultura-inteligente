<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\CitaController;

// Rutas públicas para discusiones
Route::get('temas', [TemaController::class, 'index']);
Route::get('temas/{id}', [TemaController::class, 'show']);

// Rutas para paginado
Route::get('comentarios-tema/{id}', [ComentarioController::class, 'index']);
Route::get('citas-comentario/{id}', [CitaController::class, 'index']);

// Rutas para discusiones
Route::middleware(['auth.service', 'role:admin,consumidor,vendedor'])->group(function () {
    Route::post('temas', [TemaController::class, 'store']);
    Route::put('temas/{id}', [TemaController::class, 'update']);
    Route::delete('temas/{id}', [TemaController::class, 'destroy']);
});

// Rutas para comentarios
Route::middleware(['auth.service', 'role:admin,consumidor,vendedor'])->group(function () {
    Route::post('crear-comentario/{id}', [ComentarioController::class, 'store']);
    Route::put('comentario/{id}', [ComentarioController::class, 'update']);
    Route::delete('comentario/{id}', [ComentarioController::class, 'destroy']);
});

// Rutas para citas
Route::middleware(['auth.service', 'role:admin,consumidor,vendedor'])->group(function () {
    Route::post('crear-cita/{id}', [CitaController::class, 'store']);
    Route::put('citas/{id}', [CitaController::class, 'update']);
    Route::delete('citas/{id}', [CitaController::class, 'destroy']);
});