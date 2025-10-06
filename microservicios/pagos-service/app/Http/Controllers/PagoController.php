<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    public function store(Request $request) {

        // Log del token recibido
        Log::info('Token recibido en servicio de pagos', [
        'token' => $request->bearerToken()
        ]);
        $validator = Validator::make($request->all(), [
            'pedido_id'       => 'required|integer',
            'usuario_id'      => 'required|integer',
            'metodo_pago_id'  => 'required|exists:metodos_pago,id',
            'monto'           => 'required|numeric|min:0',
            // Aquí podrían venir los datos de la tarjeta
            'tarjeta.numero'  => 'required|string|min:16|max:16',
            'tarjeta.exp'     => 'required|string',
            'tarjeta.cvv'     => 'required|string|min:3|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Validar si el método de pago está activo
        $metodo = MetodoPago::find($request->metodo_pago_id);
        if (!$metodo || !$metodo->activo) {
            return response()->json(['message' => 'Método de pago no disponible'], 400);
        }

        // Aquí debería entrar la integración con pasarela de pagos real
        $estadoPago = 'aprobado'; // Simulación

        $pago = Pago::create([
            'pedido_id'      => $request->pedido_id,
            'usuario_id'     => $request->usuario_id,
            'metodo_pago_id' => $request->metodo_pago_id,
            'monto'          => $request->monto,
            'estado'         => $estadoPago,
        ]);

        return response()->json([
            'message' => 'Pago procesado',
            'pago'    => $pago
        ], 201);
    }
}