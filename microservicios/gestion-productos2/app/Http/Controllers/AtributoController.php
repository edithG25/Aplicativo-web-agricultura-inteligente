<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Atributo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AtributoController extends Controller
{
    public function index()
    {
        $atributos = Atributo::all();
        return response()->json([
            'mensaje' => 'Atributos obtenidos exitosamente.',
            'atributos' => $atributos
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:atributos,nombre',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $atributo = Atributo::create($request->all());

        return response()->json([
            'mensaje' => 'Atributo creado exitosamente.',
            'atributo' => $atributo
        ], 201);
    }

    public function show($id)
    {
        $atributo = Atributo::find($id);

        if (!$atributo) {
            return response()->json([
                'mensaje' => 'Atributo no encontrado.'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Atributo obtenido exitosamente.',
            'atributo' => $atributo
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $atributo = Atributo::find($id);

        if (!$atributo) {
            return response()->json([
                'mensaje' => 'Atributo no encontrado.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:255|unique:atributos,nombre,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors()
            ], 400);
        }

        $atributo->update($request->all());

        return response()->json([
            'mensaje' => 'Atributo actualizado exitosamente.',
            'atributo' => $atributo
        ], 200);
    }

    public function destroy($id)
    {
        $atributo = Atributo::find($id);

        if (!$atributo) {
            return response()->json([
                'mensaje' => 'Atributo no encontrado.'
            ], 404);
        }

        $atributo->delete();

        return response()->json([
            'mensaje' => 'Atributo eliminado exitosamente.'
        ], 200);
    }
}
