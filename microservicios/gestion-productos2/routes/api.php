<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AtributoController;
use App\Http\Controllers\ReseniaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Rutas públicas (no requieren autenticación/autorización en este microservicio)
Route::get('productos', [ProductoController::class, 'index']);
Route::get('productos/{id}', [ProductoController::class, 'show']);
Route::get('categorias', [CategoriaController::class, 'index']);
Route::get('categorias/{id}', [CategoriaController::class, 'show']);
Route::get('atributos', [AtributoController::class, 'index']);
Route::get('atributos/{id}', [AtributoController::class, 'show']);
Route::get('resenias', [ReseniaController::class, 'index']);
Route::get('resenias/{id}', [ReseniaController::class, 'show']);
Route::patch('productos/{id}/reducir-stock', [ProductoController::class, 'reducirStock']);


// Rutas que requieren autorización (simulada mediante headers)
// La lógica de autorización se encuentra dentro de cada método del controlador.
// Se espera que un API Gateway o un microservicio de autenticación
// inyecte los headers X-User-ID y X-User-Role.

// Rutas de Productos
Route::middleware(['auth.service', 'role:admin,vendedor'])->group(function () {
    Route::get('mis-productos', [ProductoController::class, 'showSellerProducts']);
    Route::post('productos', [ProductoController::class, 'store']);
    Route::put('productos/{id}', [ProductoController::class, 'update']);
    Route::delete('productos/{id}', [ProductoController::class, 'destroy']);
});
// Route::post('productos/{id}/promocionar', [ProductoController::class, 'promocionar']);
// Route::get('productos/{id}/estadisticas-venta', [ProductoController::class, 'verEstadisticasVenta']);
// Route::post('productos/{id}/anadir-carrito', [ProductoController::class, 'anadirAlCarrito']);
// Route::put('productos/{id}/actualizar-stock', [ProductoController::class, 'actualizarStock']); // Ruta interna

// Rutas de Categorías
Route::middleware(['auth.service', 'role:admin'])->group(function () {
    Route::post('categorias', [CategoriaController::class, 'store']);
    Route::put('categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('categorias/{id}', [CategoriaController::class, 'destroy']);
});

// Rutas de Atributos
Route::middleware(['auth.service', 'role:admin'])->group(function () {
    Route::post('atributos', [AtributoController::class, 'store']);
    Route::put('atributos/{id}', [AtributoController::class, 'update']);
    Route::delete('atributos/{id}', [AtributoController::class, 'destroy']);
});

// Rutas de Reseñas
Route::middleware(['auth.service', 'role:user,admin,vendedor'])->group(function () {
    Route::post('/resenias', [ReseniaController::class, 'store']);

    // Actualizar una reseña (solo autor o admin)
    Route::put('/resenias/{id}', [ReseniaController::class, 'update']);

    // Eliminar una reseña (solo autor o admin)
    Route::delete('/resenias/{id}', [ReseniaController::class, 'destroy']);
});

