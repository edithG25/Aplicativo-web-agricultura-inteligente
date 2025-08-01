<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Registro de usuario
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'password' => 'required|string|min:6|confirmed',
            'rol_id' => 'required|exists:roles,id', // Asegura que el rol exista
        ]);

        $user = User::create([
            'nombre' => $validated['nombre'],
            'password' => Hash::make($validated['password']),
            'rol_id' => $validated['rol_id'],
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user,
        ], 201);
    }

    // Login de usuario por nombre
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nombre' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('nombre', $credentials['nombre'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('rol'), // Incluye la relación con el rol
        ]);
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    // Obtener usuario autenticado
    public function me(Request $request)
    {
        return response()->json($request->user()->load('rol'));
    }
}
