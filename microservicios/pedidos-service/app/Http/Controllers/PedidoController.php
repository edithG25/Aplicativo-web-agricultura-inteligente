<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\CarroCompra;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class PedidoController extends Controller
{

    // Crear un pedido a partir de un carrito
    public function createOrder(Request $request)
    {
        $user = $request->user(); // Aquí se obtiene el usuario del token

        // Validar dirección de envío
        $validator = Validator::make($request->all(), [
            'nombre_recibe'  => 'required|string|max:100',
            'telefono'      => 'required|string|max:10',
            'direccion_envio' => 'required|string|max:500',
            'metodo_pago_id'  => 'required|integer',
            'tarjeta.numero'  => 'required|string|min:16|max:16',
            'tarjeta.exp'     => 'required|string',
            'tarjeta.cvv'     => 'required|string|min:3|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $cart = CarroCompra::where('usuario_id', $user->id)
            ->where('estado', 'activo')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Carrito activo no encontrado o vacío.'], 404);
        }

        // Calcular el total del pedido
        $totalAmount = $cart->items->sum(function ($item) {
            return $item->cantidad * $item->precio_en_carrito;
        });

        try {
            DB::beginTransaction();

            // Crear pedido
            $order = Pedido::create([
                'usuario_id'      => $user->id,
                'total_monto'     => $totalAmount,
                'estado'          => 'pendiente',
                'direccion_envio' => $request->direccion_envio,
                'nombre_recibe'   => $request->nombre_recibe,
                'telefono'        => $request->telefono,
            ]);

            // Mover ítems del carrito al pedido
            foreach ($cart->items as $item) {

                // Crear el item en el pedido
                PedidoItem::create([
                    'pedido_id'              => $order->id,
                    'producto_id'            => $item->producto_id,
                    'id_vendedor'            => $item->id_vendedor, // si lo guardas en el carrito
                    'cantidad'               => $item->cantidad,
                    'precio_momento_pedido'  => $item->precio_en_carrito,
                    'producto_subtotal'      => $item->cantidad * $item->precio_en_carrito,
                ]);
            }

            // Aquí es donde se envía el token al servicio de pagos
            $response = Http::withToken($request->bearerToken())
                ->post(env('PAGOS_SERVICE_URL') . '/api/pagos', [
                    'pedido_id'      => $order->id,
                    'usuario_id'     => $user->id,
                    'metodo_pago_id' => $request->metodo_pago_id,
                    'monto'          => $totalAmount,
                    'tarjeta'        => [
                        'numero' => $request->tarjeta['numero'],
                        'exp'    => $request->tarjeta['exp'],
                        'cvv'    => $request->tarjeta['cvv'],
                    ] 
                ]);

            if ($response->failed()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error al procesar pago',
                    'error'   => $response->json()
                ], 400);
            }

            $pago = $response->json()['pago'];

            // Si el pago fue aprobado
            if ($pago['estado'] === 'aprobado') {

                foreach ($cart->items as $item) {

                    // Reducir stock en el servicio de productos
                    $response = Http::withToken($request->bearerToken())
                        ->patch(env('PRODUCT_SERVICE_URL') . "/{$item->producto_id}/reducir-stock", [
                            'cantidad' => $item->cantidad,
                        ]);

                    if ($response->failed()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error reduciendo stock de producto',
                            'producto_id' => $item->producto_id,
                            'error' => $response->json()
                        ], 400);
                    }
                }

                $order->estado = 'pagado';
                $order->save();

                $cart->estado = 'completed';
                $cart->save();

                // Acceder al servicio de productos para reducir stock

                $cart->items()->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Pedido realizado y pagado',
                    'pedido'  => $order->load('items'),
                    'pago'    => $pago
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Pago rechazado',
                    'pago'    => $pago
                ], 402); // 402 Payment Required
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creando el pedido.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Obtener un pedido específico por ID
    public function show(string $orderId)
    {
        $order = Pedido::with('items')->find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        return response()->json($order);
    }

    // Obtener los pedidos de un vendedor específico
    public function sellerOrders(Request $request)
    {
        $vendedorId = $request->user()->id;

        // Obtener solo los items de pedidos que pertenecen a este vendedor
        $items = PedidoItem::where('id_vendedor', $vendedorId)
            ->with('order') // relación belongsTo en PedidoItem
            ->get();

        foreach ($items as $item) {
            if ($item->order) {
                $usuarioId = $item->order->usuario_id;
            } else {
                \Log::warning("El item {$item->id} no tiene un pedido asociado");
            }
        }
        
        $resultados = $items->map(function ($item) {
            // --- Usuario ---
            $usuarioNombre = 'No disponible';
            try {
                $response = Http::get(env('AUTH_SERVICE_URL') . '/api/usuarios/' . $item->order->usuario_id);
                $usuarioNombre = $response->json()['nombre'] ?? 'Desconocido';
            } catch (\Exception $e) {
                \Log::error("Error al obtener el nombre del usuario: " . $e->getMessage());
            }

            // --- Producto ---
            $productoNombre = 'No disponible';
            $atributos = [];
            try {
                $respProd = Http::get(env('PRODUCT_SERVICE_URL') . '/' . $item->producto_id);
                $productoData = $respProd->json()['producto'] ?? null;

                if ($productoData) {
                    $productoNombre = $productoData['nombre'] ?? 'Desconocido';

                    // Mapear solo nombre y valor
                    $atributos = collect($productoData['atributos'] ?? [])->map(function ($atributo) {
                        return [
                            'nombre' => $atributo['nombre'] ?? 'Sin nombre',
                            'valor'  => $atributo['pivot']['valor'] ?? null,
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                \Log::error("Error al obtener datos del producto: " . $e->getMessage());
            }

            return [
                'pedido_id'       => $item->pedido_id,
                'usuario_id'      => $item->order->usuario_id,
                'usuario_nombre'  => $usuarioNombre,
                'direccion_envio' => $item->order->direccion_envio ?? 'No disponible', // <-- aquí la dirección
                'estado'          => $item->order->estado ?? 'No disponible',
                'fecha_orden'     => $item->order->created_at,
                'producto_id'     => $item->producto_id,
                'producto_nombre' => $productoNombre,
                'atributos'       => $atributos,
                'cantidad'        => $item->cantidad,
                'precio'          => $item->precio_momento_pedido,
                'subtotal'        => $item->producto_subtotal,
            ];
        });


        return response()->json($resultados);
    }


    // Obtener todos los pedidos de un usuario
    public function userOrders(Request $request)
    {
        $user = $request->user(); // usuario autenticado

        $orders = Pedido::where('usuario_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $resultados = $orders->map(function ($order) {
            // recorremos los items del pedido
            $order->items = $order->items->map(function ($item) {
                $productoNombre = 'No disponible';

                try {
                    $respProd = Http::get(env('PRODUCT_SERVICE_URL') . '/' . $item->producto_id);
                    $productoData = $respProd->json()['producto'] ?? null;

                    if ($productoData) {
                        $productoNombre = $productoData['nombre'] ?? 'Desconocido';
                    }
                } catch (\Exception $e) {
                    \Log::error("Error al obtener datos del producto: " . $e->getMessage());
                }

                // añadimos el campo al item
                $item->producto_nombre = $productoNombre;

                return $item;
            });

            return $order;
        });

        return response()->json($resultados);
    }

    // Actualizar el estado de un pedido (ej. por un administrador o por el servicio de pagos)
    public function updateStatus(Request $request, string $orderId)
    {
        $validator = Validator::make($request->all(), [
            'estado' => ['required', 'string', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $order = Pedido::find($orderId);
            if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        $order->estado = $request->estado;
        $order->save();
        return response()->json($order, 200);
    }
    
    // Opcional: Eliminar un pedido (con precaución, generalmente no se eliminan pedidos)
    public function destroy(string $orderId)
    {
        $order = Pedido::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully.'], 200);
    }
}
