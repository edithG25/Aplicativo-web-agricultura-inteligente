<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetodoPagoController extends Controller
{
    /**
     * Mostrar todos los métodos de pago
     */
    public function index()
    {
        return response()->json(MetodoPago::all(), 200);
    }

    /**
     * Crear un nuevo método de pago
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $metodo = MetodoPago::create([
            'nombre' => $request->nombre,
            'activo' => $request->activo ?? true,
        ]);

        return response()->json([
            'message' => 'Método de pago creado',
            'metodo'  => $metodo
        ], 201);
    }

    /**
     * Mostrar un método de pago por id
     */
    public function show($id)
    {
        $metodo = MetodoPago::find($id);

        if (!$metodo) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        return response()->json($metodo, 200);
    }

    /**
     * Actualizar un método de pago
     */
    public function update(Request $request, $id)
    {
        $metodo = MetodoPago::find($id);

        if (!$metodo) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:100',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $metodo->update($request->only(['nombre', 'activo']));

        return response()->json([
            'message' => 'Método de pago actualizado',
            'metodo'  => $metodo
        ], 200);
    }

    /**
     * Eliminar un método de pago
     */
    public function destroy($id)
    {
        $metodo = MetodoPago::find($id);

        if (!$metodo) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        $metodo->delete();

        return response()->json(['message' => 'Método de pago eliminado'], 200);
    }
}
