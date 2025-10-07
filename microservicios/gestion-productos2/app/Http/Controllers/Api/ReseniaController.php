<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resenia;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReseniaController extends Controller
{
    public function index(Request $request)
    {
        $query = Resenia::query();

        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        if ($request->has('calificacion_min')) {
            $query->where('calificacion', '>=', $request->calificacion_min);
        }

        $resenias = $query->paginate(10);

        return response()->json([
            'mensaje' => 'Reseñas obtenidas exitosamente.',
            'reseñas' => $resenias
        ], 200);
    }

    /**
     * Almacenar una nueva reseña. (RF11: Creación de reseñas)
     * Requiere rol de consumidor (simulado).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Simulación de autorización:
        $userId = $request->header('X-User-ID');
        $userRole = $request->header('X-User-Role');

        if (!$userId || !$userRole || $userRole !== 'consumidor') {
            return response()->json(['mensaje' => 'Acceso no autorizado. Se requiere rol de consumidor.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:productos,id',
            // 'usuario_id' => 'required|integer', // Ya lo obtenemos del header
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $reseniaData = $request->all();
        $reseniaData['usuario_id'] = $userId; // Asignar el ID del usuario del header

        $resenia = Resenia::create($reseniaData);

        return response()->json([
            'mensaje' => 'Reseña creada exitosamente.',
            'reseña' => $resenia
        ], 201);
    }

    public function show($id)
    {
        $resenia = Resenia::find($id);

        if (!$resenia) {
            return response()->json([
                'mensaje' => 'Reseña no encontrada.'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Reseña obtenida exitosamente.',
            'reseña' => $resenia
        ], 200);
    }

    /**
     * Actualizar una reseña específica.
     * Requiere ser el autor de la reseña o un administrador (simulado).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $resenia = Resenia::find($id);

        if (!$resenia) {
            return response()->json([
                'mensaje' => 'Reseña no encontrada.'
            ], 404);
        }

        // Simulación de autorización:
        $userId = $request->header('X-User-ID');
        $userRole = $request->header('X-User-Role');

        if (!$userId || !$userRole || ($resenia->usuario_id != $userId && $userRole !== 'administrador')) {
            return response()->json(['mensaje' => 'Acceso no autorizado. No eres el autor de esta reseña ni un administrador.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'calificacion' => 'integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $resenia->update($request->all());

        return response()->json([
            'mensaje' => 'Reseña actualizada exitosamente.',
            'reseña' => $resenia
        ], 200);
    }

    /**
     * Eliminar una reseña específica.
     * Requiere ser el autor de la reseña o un administrador (simulado).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $resenia = Resenia::find($id);

        if (!$resenia) {
            return response()->json([
                'mensaje' => 'Reseña no encontrada.'
            ], 404);
        }

        // Simulación de autorización:
        $userId = request()->header('X-User-ID');
        $userRole = request()->header('X-User-Role');

        if (!$userId || !$userRole || ($resenia->usuario_id != $userId && $userRole !== 'administrador')) {
            return response()->json(['mensaje' => 'Acceso no autorizado. No eres el autor de esta reseña ni un administrador.'], 403);
        }

        $resenia->delete();

        return response()->json([
            'mensaje' => 'Reseña eliminada exitosamente.'
        ], 200);
    }
}
