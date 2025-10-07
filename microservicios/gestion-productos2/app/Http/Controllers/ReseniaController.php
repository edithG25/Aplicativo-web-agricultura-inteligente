<?php

namespace App\Http\Controllers;

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
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:productos,id',
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
        $reseniaData['usuario_id'] = $user->id; 

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

        $user = $request->user();

        // Permiso: autor o admin
        if ($resenia->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para actualizar esta reseña.'
            ], 403);
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
    public function destroy(Request $request, $id)
    {
        $resenia = Resenia::find($id);

        if (!$resenia) {
            return response()->json([
                'mensaje' => 'Reseña no encontrada.'
            ], 404);
        }

        $user = $request->user();

        if ($resenia->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para eliminar esta reseña.'
            ], 403);
        }

        $resenia->delete();

        return response()->json([
            'mensaje' => 'Reseña eliminada exitosamente.'
        ], 200);
    }
}
