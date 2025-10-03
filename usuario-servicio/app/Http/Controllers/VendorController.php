<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class VendorController extends Controller
{
    // Listar todos los vendedores
    public function index()
    {
        $vendors = User::where('role', 'vendedor')
            ->select('id', 'name', 'email', 'nombre_tienda', 'razon_social', 'telefono', 'categoria_productos', 'photo')
            ->get();

        return response()->json([
            'status' => 'success',
            'vendors' => $vendors
        ]);
    }

    // Mostrar un vendedor específico
    public function show($id)
    {
        $vendor = User::where('role', 'vendedor')
            ->where('id', $id)
            ->select('id', 'name', 'email', 'nombre_tienda', 'razon_social', 'telefono', 'categoria_productos')
            ->first();

        if (!$vendor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vendedor no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'vendor' => $vendor
        ]);
    }
}
