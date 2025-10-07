<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Atributo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class ProductoController extends Controller
{
    
    /**
     * Mostrar una lista de productos. (RF03.1: ver productos)
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Producto::with(['categorias', 'atributos', 'resenias']);

        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }
        if ($request->has('precio_min')) {
            $query->where('precio', '>=', $request->precio_min);
        }
        if ($request->has('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }
        if ($request->has('categoria_id')) {
            $query->whereHas('categorias', function ($q) use ($request) {
                $q->where('categorias.id', $request->categoria_id);
            });
        }
        if ($request->has('categoria_nombre')) {
            $query->whereHas('categorias', function ($q) use ($request) {
                $q->where('categorias.nombre', 'like', '%' . $request->categoria_nombre . '%');
            });
        }
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->has('promocionado')) {
            $query->where('promocionado', (bool)$request->promocionado);
        }

        $productos = $query->paginate(10);
        
        foreach ($productos as $producto) {
            try {
                $response = Http::get(env('AUTH_SERVICE_URL') . '/api/usuarios/' . $producto->id_vendedor);
                $producto->usuario_nombre = $response->json()['nombre'] ?? 'Desconocido';
            } catch (\Exception $e) {
                $producto->usuario_nombre = 'No disponible';
            }
        }

        return response()->json([
            'mensaje' => 'Productos obtenidos exitosamente.',
            'productos' => $productos
        ], 200);
    }

    /**
     * Almacenar un nuevo producto. (RF03.2: agregar productos)
     * Requiere id_vendedor y rol 'vendedor' o 'administrador' (simulado).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user(); // ← aquí obtienes el usuario del token

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen_url' => 'nullable|url',
            'estado' => 'in:activo,pausado,eliminado',
            'promocionado' => 'boolean',
            'categorias' => 'array',
            'categorias.*' => 'exists:categorias,id',
            'atributos' => 'array',
            'atributos.*.id' => 'required|exists:atributos,id',
            'atributos.*.valor' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $productoData = $request->except(['categorias', 'atributos']);
        $productoData['id_vendedor'] = $user->id;

        $producto = Producto::create($productoData);

        if ($request->has('categorias')) {
            $producto->categorias()->attach($request->categorias);
        }
        if ($request->has('atributos')) {
            $atributosData = [];
            foreach ($request->atributos as $attr) {
                $atributosData[$attr['id']] = ['valor' => $attr['valor']];
            }
            $producto->atributos()->attach($atributosData);
        }

        return response()->json([
            'mensaje' => 'Producto creado exitosamente.',
            'producto' => $producto->load(['categorias', 'atributos'])
        ], 201);
    }

    /**
     * Mostrar un producto específico. (RF03.1: ver productos)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $producto = Producto::with(['categorias', 'atributos', 'resenias'])->find($id);

        if (!$producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado.'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Producto obtenido exitosamente.',
            'producto' => $producto
        ], 200);
    }

    public function showSellerProducts(Request $request)
    {
        $user = $request->user();

        $productos = Producto::with(['categorias', 'atributos', 'resenias'])
            ->where('id_vendedor', $user->id) // Asegura que el vendedor solo vea sus propios productos
            ->paginate(10);

        return response()->json([
            'mensaje' => 'Productos del vendedor obtenidos exitosamente.',
            'productos' => $productos
        ], 200);
    }

    /**
     * Actualizar un producto específico. (RF03.6: actualizar la información)
     * Requiere ser el vendedor del producto o un administrador (simulado).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'numeric|min:0',
            'stock' => 'integer|min:0',
            'imagen_url' => 'nullable|url',
            'estado' => 'in:activo,pausado,eliminado',
            'promocionado' => 'boolean',
            'categorias' => 'array',
            'categorias.*' => 'exists:categorias,id',
            'atributos' => 'array',
            'atributos.*.id' => 'required|exists:atributos,id',
            'atributos.*.valor' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $producto->update($request->except(['categorias', 'atributos']));

        if ($request->has('categorias')) {
            $producto->categorias()->sync($request->categorias);
        }
        if ($request->has('atributos')) {
            $atributosData = [];
            foreach ($request->atributos as $attr) {
                $atributosData[$attr['id']] = ['valor' => $attr['valor']];
            }
            $producto->atributos()->sync($atributosData);
        }

        return response()->json([
            'mensaje' => 'Producto actualizado exitosamente.',
            'producto' => $producto->load(['categorias', 'atributos'])
        ], 200);
    }

    /**
     * Eliminar un producto específico. (RF03.5: eliminar productos)
     * Requiere ser el vendedor del producto o un administrador (simulado).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado.'
            ], 404);
        }

        // Acceder a los carritos con el producto para eliminarlos

        $response = Http::withToken($request->bearerToken())
            ->delete(config('services.cartitems.url') . "/deleteproducts/{$id}");
        if ($response->failed()) {
                return response()->json(['error' => 'No se pudieron eliminar los productos de los carros'], 400);
        }
        
        $producto->delete();

        return response()->json([
            'mensaje' => 'Producto eliminado exitosamente.'
        ], 200);
    }

    public function reducirStock(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail($id);

        if ($producto->stock < $request->cantidad) {
            return response()->json(['error' => 'Stock insuficiente'], 400);
        }

        $producto->stock -= $request->cantidad;
        $producto->save();

        return response()->json(['message' => 'Stock actualizado']);
    }

    /**
     * Promocionar un producto. (RF03.3: promocionar productos)
     * Requiere rol de administrador (simulado).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function promocionar($id)
    // {
    //     // Simulación de autorización:
    //     $userRole = request()->header('X-User-Role');
    //     if (!$userRole || $userRole !== 'administrador') {
    //         return response()->json(['mensaje' => 'Acceso no autorizado. Se requiere rol de administrador.'], 403);
    //     }

    //     $producto = Producto::find($id);

    //     if (!$producto) {
    //         return response()->json([
    //             'mensaje' => 'Producto no encontrado.'
    //         ], 404);
    //     }

    //     $producto->promocionado = !$producto->promocionado;
    //     $producto->save();

    //     try {
    //         $client = new Client();
    //         $response = $client->post(env('MICROSERVICIO_NOTIFICACIONES_URL') . '/api/notificar-promocion', [
    //             'json' => [
    //                 'id_producto' => $producto->id,
    //                 'nombre_producto' => $producto->nombre,
    //                 'mensaje' => $producto->promocionado ? '¡Gran oferta! ' . $producto->nombre . ' ahora en promoción.' : $producto->nombre . ' ya no está en promoción.'
    //             ]
    //         ]);

    //         if ($response->getStatusCode() == 200) {
    //             return response()->json([
    //                 'mensaje' => 'Producto ' . ($producto->promocionado ? 'promocionado' : 'despromocionado') . ' exitosamente y notificación enviada.'
    //             ], 200);
    //         } else {
    //             return response()->json([
    //                 'mensaje' => 'Producto ' . ($producto->promocionado ? 'promocionado' : 'despromocionado') . ', pero hubo un error al enviar la notificación.',
    //                 'respuesta_notificacion' => json_decode($response->getBody()->getContents(), true)
    //             ], 500);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'mensaje' => 'Producto ' . ($producto->promocionado ? 'promocionado' : 'despromocionado') . ', pero hubo un error de conexión con el servicio de notificaciones.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Ver estadísticas de venta de un producto. (RF06.1)
     * Requiere rol de vendedor o administrador (simulado).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function verEstadisticasVenta($id)
    // {
    //     // Simulación de autorización:
    //     $userId = request()->header('X-User-ID');
    //     $userRole = request()->header('X-User-Role');

    //     $producto = Producto::find($id);

    //     if (!$producto) {
    //         return response()->json([
    //             'mensaje' => 'Producto no encontrado.'
    //         ], 404);
    //     }

    //     if (!$userId || !$userRole || ($producto->id_vendedor != $userId && $userRole !== 'administrador')) {
    //         return response()->json(['mensaje' => 'Acceso no autorizado. No eres el vendedor de este producto ni un administrador.'], 403);
    //     }

    //     try {
    //         $client = new Client();
    //         $response = $client->get(env('MICROSERVICIO_PEDIDOS_URL') . '/api/pedidos/estadisticas-producto/' . $id);

    //         $estadisticas = json_decode($response->getBody()->getContents(), true);

    //         return response()->json([
    //             'mensaje' => 'Estadísticas de venta obtenidas exitosamente.',
    //             'producto' => $producto->nombre,
    //             'estadisticas' => $estadisticas
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'mensaje' => 'Error al obtener estadísticas de venta del microservicio de pedidos.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Añadir productos al carrito (RF04.1)
     * Requiere rol de consumidor (simulado).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function anadirAlCarrito(Request $request, $id)
    // {
    //     // Simulación de autorización:
    //     $userId = $request->header('X-User-ID');
    //     $userRole = $request->header('X-User-Role');

    //     if (!$userId || !$userRole || $userRole !== 'consumidor') {
    //         return response()->json(['mensaje' => 'Acceso no autorizado. Se requiere rol de consumidor.'], 403);
    //     }

    //     $producto = Producto::find($id);

    //     if (!$producto) {
    //         return response()->json([
    //             'mensaje' => 'Producto no encontrado.'
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         // 'id_usuario' => 'required|integer', // Ya lo obtenemos del header
    //         'cantidad' => 'required|integer|min:1|max:' . $producto->stock,
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'mensaje' => 'Error de validación.',
    //             'errores' => $validator->errors()
    //         ], 400);
    //     }

    //     try {
    //         $client = new Client();
    //         $response = $client->post(env('MICROSERVICIO_CARRITO_URL') . '/api/carrito/agregar', [
    //             'json' => [
    //                 'id_usuario' => $userId, // Usar el ID del usuario del header
    //                 'id_producto' => $producto->id,
    //                 'cantidad' => $request->cantidad,
    //                 'precio_unitario' => $producto->precio,
    //             ]
    //         ]);

    //         if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
    //             return response()->json([
    //                 'mensaje' => 'Producto añadido al carrito exitosamente.'
    //             ], 200);
    //         } else {
    //             return response()->json([
    //                 'mensaje' => 'Error al añadir producto al carrito en el microservicio de carrito.',
    //                 'respuesta_carrito' => json_decode($response->getBody()->getContents(), true)
    //             ], $response->getStatusCode());
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'mensaje' => 'Error de conexión con el microservicio de carrito.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Actualizar stock de un producto.
     * Esta ruta debería ser llamada internamente por el microservicio de Pedidos.
     * Podría requerir un token de servicio a servicio o estar en una red privada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function actualizarStock(Request $request, $id)
    // {
    //     // Para rutas internas, podrías verificar un token de API pre-compartido
    //     // o simplemente confiar en que la llamada viene de un servicio autorizado
    //     // si están en una red privada.
    //     // if ($request->header('X-Service-Token') !== env('INTERNAL_SERVICE_TOKEN')) {
    //     //     return response()->json(['mensaje' => 'Acceso no autorizado para servicio interno.'], 403);
    //     // }

    //     $producto = Producto::find($id);

    //     if (!$producto) {
    //         return response()->json([
    //             'mensaje' => 'Producto no encontrado.'
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'cantidad' => 'required|integer',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'mensaje' => 'Error de validación.',
    //             'errores' => $validator->errors()
    //         ], 400);
    //     }

    //     $nuevoStock = $producto->stock + $request->cantidad;

    //     if ($nuevoStock < 0) {
    //         return response()->json([
    //             'mensaje' => 'Stock insuficiente para la operación.'
    //         ], 400);
    //     }

    //     $producto->stock = $nuevoStock;
    //     $producto->save();

    //     return response()->json([
    //         'mensaje' => 'Stock del producto actualizado exitosamente.',
    //         'producto' => $producto
    //     ], 200);
    // }
}
