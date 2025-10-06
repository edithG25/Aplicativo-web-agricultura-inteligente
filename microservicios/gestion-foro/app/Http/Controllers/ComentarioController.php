<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comentario;
use App\Models\Tema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class ComentarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(String $temaId)
    {
        $comentarios = Comentario::where('tema_id', $temaId)->paginate(10);

        foreach ($comentarios as $comentario) {
            try {
                $response = Http::get(env('AUTH_SERVICE_URL') . '/api/usuarios/' . $comentario->usuario_id);
                $comentario->usuario_nombre = $response->json()['nombre'] ?? 'Desconocido';
            } catch (\Exception $e) {
                $comentario->usuario_nombre = 'No disponible';
            }
        }

        return $comentarios;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
    {
        $user = $request->user();

        // Validar que el tema exista
        $tema = Tema::find($id);
        if (!$tema) {
            return response()->json(['error' => 'Tema no encontrado'], 404);
        }

        $request->validate([
            'contenido' => 'nullable|string',
            'imagen_url' => 'nullable|url',
        ]);

        $request->merge([
            'usuario_id' => $user->id,
            'tema_id' => $id
        ]);

        $comentario = Comentario::create($request->all());
        return response()->json($comentario, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $comentario = Comentario::with(['citas'])->findOrFail($id);

        if (!$comentario) {
            return response()->json([
                'mensaje' => 'Comentario no encontrada.'
            ], 404);
        }
        
        return $comentario;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $comentario = Comentario::findOrFail($id);

        if (!$comentario) {
            return response()->json([
                'mensaje' => 'Comentario no encontrado.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'contenido' => 'nullable|string',
            'imagen_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $user = $request->user();

        if ($comentario->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para actualizar este comentario.'
            ], 403);
        }

        $comentario->update($request->all());

        return response()->json($comentario, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $comentario = Comentario::findOrFail($id);

        if (!$comentario) {
            return response()->json([
                'mensaje' => 'Comentario no encontrado.'
            ], 404);
        }

        $user = $request->user();

        if ($comentario->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para eliminar este comentario.'
            ], 403);
        }

        $comentario->destroy($id);
        
        return response()->json([
            'mensaje' => 'Comentario eliminada exitosamente.'
        ], 200);
    }
}
