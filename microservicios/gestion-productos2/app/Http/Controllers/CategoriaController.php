<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    /**
     * Mostrar una lista de categorías. (RF09.1)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias = Categoria::all();
        return response()->json([
            'mensaje' => 'Categorías obtenidas exitosamente.',
            'categorias' => $categorias
        ], 200);
    }

    /**
     * Almacenar una nueva categoría. (RF09.1)
     * Requiere rol de administrador (simulado).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $categoria = Categoria::create($request->all());

        return response()->json([
            'mensaje' => 'Categoría creada exitosamente.',
            'categoria' => $categoria
        ], 201);
    }

    /**
     * Mostrar una categoría específica.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'mensaje' => 'Categoría no encontrada.'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Categoría obtenida exitosamente.',
            'categoria' => $categoria
        ], 200);
    }

    /**
     * Actualizar una categoría específica. (RF09.1)
     * Requiere rol de administrador (simulado).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'mensaje' => 'Categoría no encontrada.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $categoria->update($request->all());

        return response()->json([
            'mensaje' => 'Categoría actualizada exitosamente.',
            'categoria' => $categoria
        ], 200);
    }

    /**
     * Eliminar una categoría específica. (RF09.1)
     * Requiere rol de administrador (simulado).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'mensaje' => 'Categoría no encontrada.'
            ], 404);
        }

        $categoria->delete();

        return response()->json([
            'mensaje' => 'Categoría eliminada exitosamente.'
        ], 200);
    }
}
