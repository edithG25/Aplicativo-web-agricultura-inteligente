<?php

namespace App\Http\Controllers;
use App\Models\Tema;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class TemaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Aplicar filtros por tema
        $query = Tema::with('tags'); // ya no traemos comentarios/citas aquí

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);

            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('nombre', $tags);
            });
        }

        $temas = $query->paginate(10); // solo temas + tags

        // Enriquecer con nombre de usuario llamando al microservicio de usuarios
        foreach ($temas as $tema) {
            try {
                $response = Http::get(env('AUTH_SERVICE_URL') . '/api/usuarios/' . $tema->usuario_id);
                $tema->usuario_nombre = $response->json()['nombre'] ?? 'Desconocido';
            } catch (\Exception $e) {
                $tema->usuario_nombre = 'No disponible';
            }
        }

        return $temas;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'contenido' => 'nullable|string',
            'imagen_url' => 'nullable|url',
            'tags' => 'array', // lista de tags
            'tags.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $request->merge(['usuario_id' => $user->id]);
        $temaData = $request;

        $tema = Tema::create($request->all());

        // Asociar tags si se proporcionan 
        if (!empty($temaData['tags'])) {

            $tagIds = collect($temaData['tags'])->map(function ($nombre) {
                return Tag::firstOrCreate(['nombre' => $nombre])->id;
            });

            $tema->tags()->sync($tagIds);
        }
        
        return response()->json($tema, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tema = Tema::with(['comentarios', 'comentarios.citas', 'tags'])->find($id);

        if (!$tema) {
            return response()->json([
                'mensaje' => 'Discusión no encontrada.'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Discusión obtenida exitosamente.',
            'reseña' => $tema
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tema = Tema::findOrFail($id);

        if (!$tema) {
            return response()->json([
                'mensaje' => 'Tema no encontrado.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'nullable|string|max:255',
            'contenido' => 'nullable|string',
            'imagen_url' => 'nullable|url',
            'tags' => 'array',
            'tags.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $user = $request->user();

        if ($tema->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para actualizar este comentario.'
            ], 403);
        }

        $tema->update($request->all());

        //Se guarda el request en una variable para manejar los tags
        $temaData = $request;

        if (isset($temaData['tags'])) {
        $tagIds = collect($temaData['tags'])->map(function ($nombre) {
                return Tag::firstOrCreate(['nombre' => $nombre])->id;
            });

            $tema->tags()->sync($tagIds);
        }   

        return response()->json($tema, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $tema = Tema::findOrFail($id);

        if (!$tema) {
            return response()->json([
                'mensaje' => 'Tema no encontrado.'
            ], 404);
        }

        $user = $request->user();

        if ($tema->usuario_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'mensaje' => 'No tienes permisos para eliminar este tema.'
            ], 403);
        }

        $tema->destroy($id);
        
        return response()->json([
            'mensaje' => 'Tema eliminado exitosamente.'
        ], 200);
    }

    // Obtener tags
}
