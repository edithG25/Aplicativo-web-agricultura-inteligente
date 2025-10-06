<?php

namespace App\Http\Controllers;

use App\Models\Pedido; // Asegúrate de importar el modelo correcto
use App\Models\DevolverPedido; // Asegúrate de importar el modelo correcto
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DevolverPedidoController extends Controller
{
    // Solicitar una devolución
    public function requestReturn(Request $request, string $orderId)
    {
        $user = $request->user(); // Aquí se obtiene el usuario del token

        $validator = Validator::make($request->all(), [
            'razonDevolucion' => 'required|string|max:1000',
            'cantidadReembolso' => 'nullable|numeric|min:0', // Opcional, si se especifica al solicitar
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $order = Pedido::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        // Verificar si ya hay una solicitud de devolución para este pedido
        if (DevolverPedido::where('pedido_id', $orderId)->whereIn('estado', ['pending', 'approved'])->exists()) {
            return response()->json(['message' => 'A return request for this order is already pending or approved.'], 409);
        }

        $devolucionData = array_merge($request->all(), [
            'pedido_id' => $orderId,
            'usuario_id' => $user->id,
            'estado' => 'pending'
        ]); 

        $returnRequest = DevolverPedido::create($devolucionData);
        // Opcional: Actualizar el estado del pedido a 'return_requested'
        // $order->status = 'return_requested';
        // $order->save();
        return response()->json($returnRequest, 201);
    }

    // Obtener una solicitud de devolución específica
    public function show(string $returnId)
    {
        $returnRequest = DevolverPedido::find($returnId);
        if (!$returnRequest) {
            return response()->json(['message' => 'Return request not found.'], 404);
        }
        return response()->json($returnRequest);
    }

    // Actualizar el estado de una solicitud de devolución (ej. por un administrador)
    public function updateStatus(Request $request, string $returnId)
    {
        $validator = Validator::make($request->all(), [
            'estado' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected', 'completed'])],
            'cantidadReembolso' => 'nullable|numeric|min:0', // Requerido si el estado es 'approved'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $returnRequest = DevolverPedido::find($returnId);
        if (!$returnRequest) {
            return response()->json(['message' => 'Return request not found.'], 404);
        }
        // Si se aprueba, se puede establecer el monto del reembolso
        if ($request->estado === 'approved' && is_null($request->cantidadReembolso)) {
            return response()->json([ 'message' => 'Refund amount is required when approving a return.'], 400);
        }
        $returnRequest->estado = $request->estado;
        if ($request->has('cantidadReembolso')) {
            $returnRequest->cantidadReembolso = $request->cantidadReembolso;
        }
        $returnRequest->save();
        // Si el estado es 'completed', se podría notificar al servicio de pagos para procesar el reembolso
        if ($returnRequest->estado === 'completed') {
            // Lógica para llamar al servicio de pagos
            // Por ejemplo: Http::post('http://payment-service.test/api/refund', ['order_id' => $returnRequest->order_id, 'amount' => $returnRequest->refund_amount]);
        }
        return response()->json($returnRequest, 200);
    }

    // Obtener todas las solicitudes de devolución (para administradores)
    public function index()
    {
        $returns = DevolverPedido::orderBy('created_at', 'desc')->get();
        return response()->json($returns);
    }
}
