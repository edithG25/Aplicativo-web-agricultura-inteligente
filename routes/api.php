<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevolverPedidoController;
use App\Http\Controllers\CarroController;
use App\Http\Controllers\PedidoController;

// Rutas para el Carrito de Compras
Route::middleware(['auth.service'])
    ->prefix('carts')
    ->group(function () {
        Route::get('/', [CarroController::class, 'show']); // Obtener carrito del usuario autenticado
        Route::post('/items', [CarroController::class, 'addItem']); // Añadir ítem al carrito
        Route::put('/items/{productId}', [CarroController::class, 'updateItem']); // Actualizar cantidad de ítem
        Route::delete('/items/{productId}', [CarroController::class, 'removeItem']); // Eliminar ítem del carrito
        Route::delete('/', [CarroController::class, 'clearCart']); // Vaciar carrito
        Route::delete('deleteproducts/{productId}', [CarroController::class, 'removeProductFromAllCarts']); // Eliminar un producto de todos los carritos
    });

// Rutas para Pedidos
Route::middleware(['auth.service'])
    ->prefix('orders')
    ->group(function () {
        Route::post('/', [PedidoController::class, 'createOrder']); // Crear pedido desde carrito
        Route::get('{orderId}', [PedidoController::class, 'show']); // Obtener un pedido específico
        Route::get('user/orders', [PedidoController::class, 'userOrders']); // Obtener todos los pedidos del usuario autenticado
        Route::middleware(['role:admin'])->group(function () {
            Route::put('{orderId}/status', [PedidoController::class, 'updateStatus']); // Actualizar estado del pedido (ej. por admin)
        });
        Route::middleware(['role:admin,vendedor'])->group(function () { // Permitir tanto admin como vendedor ver sus pedidos
            Route::get('seller/orders', [PedidoController::class, 'sellerOrders']);
        });
        // Route::delete('{orderId}', [OrderController::class, 'destroy']); // Opcional: Eliminar pedido
    });

// Rutas para Devoluciones
Route::middleware(['auth.service'])
    ->prefix('returns')
    ->group(function () {
        Route::post('{orderId}', [DevolverPedidoController::class, 'requestReturn']); // Solicitar devolución
        Route::get('{returnId}', [DevolverPedidoController::class, 'show']); // Obtener una solicitud de devolución
        // Rutas solo para admin
        Route::middleware(['role:admin'])->group(function () {
            Route::put('{returnId}/status', [DevolverPedidoController::class, 'updateStatus']); // Cambiar estado
            Route::get('/', [DevolverPedidoController::class, 'index']); // Listar todas las devoluciones
        });
    });