<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Comentario;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class CitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(String $comentarioId)
    {
        $citas = Cita::where('comentario_id', $comentarioId)->paginate(10);

        foreach ($citas as $cita) {
            try {
                $response = Http::get(env('AUTH_SERVICE_URL') . '/api/usuarios/' . $cita->usuario_id);
                $cita->usuario_nombre = $response->json()['nombre'] ?? 'Desconocido';
            } catch (\Exception $e) {
                $cita->usuario_nombre = 'No disponible';
            }
        }

        return $citas;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
    {
        $user = $request->user();

        // Validar que el comentario exista
        $comentario = Comentario::find($id);
        if (!$comentario) {
            return response()->json(['error' => 'Comentario no encontrado'], 404);
        }

        $request->validate([
            'contenido' => 'nullable|string',
            'imagen_url' => 'nullable|url',
        ]);

        $request->merge([
            'usuario_id' => $user->id,
            'comentario_id' => $id
        ]);

        $cita = Cita::create($request->all());
        return response()->json($cita, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cita = Cita::with(['comentarios'])->find($id);

        if (!$cita) {
            return response()->json([
                'mensaje' => 'Cita no encontrada.'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Cita obtenida exitosamente.',
            'reseña' => $cita
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cita = Cita::find($id);

        if (!$cita) {
            return response()->json([
                'mensaje' => 'Cita no encontrada.'
            ], 404);
        }

        $user = $request->user();

        // Permiso: autor o admin
        if ($cita->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para actualizar esta cita.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'contenido' => 'nullable|string',
            'imagen_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $cita->update($request->all());

        return response()->json([
            'mensaje' => 'Comentario actualizado exitosamente.',
            'reseña' => $cita
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $cita = Cita::find($id);

        if (!$cita) {
            return response()->json([
                'mensaje' => 'Cita no encontrada.'
            ], 404);
        }

        $user = $request->user();

        if ($cita->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para eliminar esta cita.'
            ], 403);
        }

        $cita->delete();

        return response()->json([
            'mensaje' => 'Cita eliminada exitosamente.'
        ], 200);
    }
}
