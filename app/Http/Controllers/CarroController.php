<?php

namespace App\Http\Controllers;

use App\Models\CarroCompra;
use App\Models\CarroItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class CarroController extends Controller
{
    // ✅ Obtener el carrito de un usuario (o crear uno si no existe)
    public function show(Request $request)
    {
        $user = $request->user();
        $cart = CarroCompra::firstOrCreate(['usuario_id' => $user->id]);
        $cart->load('items');

        $response = $this->formatCartResponse($cart);

        return response()->json($response, 200);
    }

    // ✅ Añadir un producto al carrito
    public function addItem(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|integer',
            'cantidad'    => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // URL del servicio de productos tal como la tienes en config/services.php
        $productServiceUrl = config('services.product.url');

        // 🔹 Obtener producto desde microservicio
        $productoResponse = Http::get($productServiceUrl . "/" . $request->producto_id);

        if ($productoResponse->failed()) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $producto = $productoResponse->json();
        $productoData = $producto['producto'] ?? $producto;

        // 🔹 Obtener o crear carrito
        $cart = CarroCompra::firstOrCreate(['usuario_id' => $user->id]);

        if ($cart->estado === 'completed') {
            $cart->estado = 'activo';
            $cart->save();
        }

        // 🔹 Buscar item en carrito
        $item = $cart->items()->where('producto_id', $request->producto_id)->first();

        if ($item) {
            $nuevaCantidad = $item->cantidad + $request->cantidad;

            if (isset($productoData['stock']) && $nuevaCantidad > $productoData['stock']) {
                return response()->json([
                    'error' => "Stock insuficiente. Disponibles: {$productoData['stock']}",
                    'cantidad_solicitada' => $nuevaCantidad
                ], 400);
            }

            $item->update([
                'cantidad' => $nuevaCantidad,
                'precio_en_carrito' => $productoData['precio'] ?? $item->precio_en_carrito,
                'producto_subtotal' => $nuevaCantidad * ($productoData['precio'] ?? $item->precio_en_carrito),
            ]);
        } else {
            if (isset($productoData['stock']) && $request->cantidad > $productoData['stock']) {
                return response()->json([
                    'error' => 'Stock insuficiente',
                    'cantidad_solicitada' => $request->cantidad
                ], 400);
            }

            $item = $cart->items()->create([
                'id_vendedor'        => $productoData['id_vendedor'] ?? null,
                'producto_id'        => $productoData['id'] ?? $request->producto_id,
                'cantidad'           => $request->cantidad,
                'precio_en_carrito'  => $productoData['precio'] ?? 0,
                'producto_subtotal'  => ($productoData['precio'] ?? 0) * $request->cantidad,
            ]);
        }

        $cart->load('items');
        $response = $this->formatCartResponse($cart);

        return response()->json($response, 200);
    }

    // ✅ Actualizar la cantidad de un producto en el carrito
    public function updateItem(Request $request, string $productId)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:0', // Permitir 0 para eliminar
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $cart = CarroCompra::where('usuario_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found for this user.'], 404);
        }

        $item = $cart->items()->where('producto_id', $productId)->first();
        if (!$item) {
            return response()->json(['message' => 'Product not found in cart.'], 404);
        }

        // Obtener producto desde microservicio
        $productServiceUrl = config('services.product.url');
        $productoResponse = Http::get($productServiceUrl . "/" . $productId);
        if ($productoResponse->failed()) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        $producto = $productoResponse->json();
        $productoData = $producto['producto'] ?? $producto;

        if ($request->cantidad === 0) {
            $item->delete();
        } else {
            if (isset($productoData['stock']) && $request->cantidad > $productoData['stock']) {
                return response()->json([
                    'error' => 'Stock insuficiente',
                    'cantidad_solicitada' => $request->cantidad
                ], 400);
            }

            $item->update([
                'cantidad' => $request->cantidad,
                'producto_subtotal' => $item->precio_en_carrito * $request->cantidad,
            ]);
        }

        $cart->load('items');
        $response = $this->formatCartResponse($cart);

        return response()->json($response, 200);
    }

    // ✅ Eliminar un producto del carrito
    public function removeItem(Request $request, string $productId)
    {
        $user = $request->user();
        $cart = CarroCompra::where('usuario_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found for this user.'], 404);
        }

        $item = $cart->items()->where('producto_id', $productId)->first();
        if (!$item) {
            return response()->json(['message' => 'Product not found in cart.'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Product removed from cart.'], 200);
    }

    // ✅ Vaciar el carrito
    public function clearCart(Request $request)
    {
        $user = $request->user();
        $cart = CarroCompra::where('usuario_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found for this user.'], 404);
        }

        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared successfully.'], 200);
    }

    // ✅ Eliminar un producto de todos los carritos
    public function removeProductFromAllCarts(string $productId)
    {
        CarroItem::where('producto_id', $productId)->delete();
        return response()->json(['message' => 'Product removed from all carts.'], 200);
    }

    // 🔹 Helper para formatear respuesta del carrito con productos desde microservicio
    private function formatCartResponse($cart)
    {
        // ej: config('services.product.url') puede ser "http://localhost:8002/api/productos"
        $productServiceUrl = config('services.product.url');

        // derivamos la base del host (quitar la parte /api/...) para concatenar paths locales si hace falta
        $productBase = preg_replace('#/api.*$#', '', $productServiceUrl);

        return [
            'id' => $cart->id,
            'usuario_id' => $cart->usuario_id,
            'estado' => $cart->estado,
            'items' => $cart->items->map(function ($i) use ($productServiceUrl, $productBase) {
                $productoResponse = Http::get($productServiceUrl . "/" . $i->producto_id);

                if ($productoResponse->failed()) {
                    return [
                        'id'       => $i->id,
                        'productId'=> $i->producto_id,
                        'name'     => 'Producto no disponible',
                        'image'    => null,
                        'price'    => (float) $i->precio_en_carrito,
                        'quantity' => (int) $i->cantidad,
                        'subtotal' => (float) $i->producto_subtotal,
                    ];
                }

                $producto = $productoResponse->json();
                $productoData = $producto['producto'] ?? $producto;

                // buscar posibles keys donde venga la imagen
                $img = $productoData['imagen_url'] 
                     ?? $productoData['imagen'] 
                     ?? $productoData['image'] 
                     ?? $productoData['url_imagen'] 
                     ?? null;

                // si existe y no es url absoluta, convertirla a absoluta usando productBase
                if ($img && !preg_match('#^https?://#i', $img)) {
                    $img = rtrim($productBase, '/') . '/' . ltrim($img, '/');
                }

                return [
                    'id'       => $i->id,
                    'productId'=> $i->producto_id,
                    'name'     => $productoData['nombre'] ?? $productoData['name'] ?? 'Sin nombre',
                    'image'    => $img,
                    'price'    => (float) $i->precio_en_carrito,
                    'quantity' => (int) $i->cantidad,
                    'subtotal' => (float) $i->producto_subtotal,
                ];
            }),
        ];
    }
}
