<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;


class AuthController extends Controller
{
    // Registro
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:100',
                'email'    => 'required|string|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'role'     => 'nullable|string|in:admin,vendedor,consumidor',
                'fecha_nacimiento' => 'nullable|date',
                'genero'   => 'nullable|string|in:masculino,femenino,otro',

                // Solo si es vendedor: (mínimos obligatorios)
                'razon_social'       => 'required_if:role,vendedor|string|max:150',
                'telefono'           => 'required_if:role,vendedor|string',
                'categoria_productos'=> 'required_if:role,vendedor|string|max:150',

                // Opcionales para vendedor
                'tipo_documento'     => 'nullable|string',
                'numero_documento'   => 'nullable|string',
                'direccion'          => 'nullable|string',

                // Datos de pago (opcionales)
                'numero_cuenta'      => 'nullable|string|max:50',
                'banco'              => 'nullable|string|max:100',
                'tipo_cuenta'        => 'nullable|string|max:100', // Texto libre ahora

                // Datos comerciales adicionales
                'nombre_tienda'      => 'nullable|string|max:150',

                // Políticas y documentos (opcionales)
                'politicas_envio'        => 'nullable|string',
                'documento_rut'          => 'nullable|file|mimes:pdf,jpg,png|max:2048',
                'documento_camcomercio'  => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        }

        // Manejo de archivos
        $docRutPath = $request->hasFile('documento_rut')
            ? $request->file('documento_rut')->store('documentos', 'public')
            : null;

        $docCamPath = $request->hasFile('documento_camcomercio')
            ? $request->file('documento_camcomercio')->store('documentos', 'public')
            : null;

        // Crear usuario
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'genero' => $request->genero,
            'razon_social' => $request->razon_social,
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'numero_cuenta' => $request->numero_cuenta,
            'banco' => $request->banco,
            'tipo_cuenta' => $request->tipo_cuenta,
            'nombre_tienda' => $request->nombre_tienda,
            'categoria_productos' => $request->categoria_productos,
            'politicas_envio' => $request->politicas_envio,
            'documento_rut' => $docRutPath,
            'documento_camcomercio' => $docCamPath,
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo generar token'], 500);
        }

        return response()->json([
            'status' => 'success',
            'user'   => $user,
            'token'  => $token
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo crear el token'], 500);
        }

        return response()->json([
            'status' => 'success',
            'user'   => auth()->user(),
            'token'  => $token
        ]);
    }

    // Perfil /me
    public function me()
    {

        $user = auth()->user();

        // Si tiene foto, generar URL pública
        if ($user->photo) {
            $user->photo_url = Storage::url($user->photo);
        }
        return response()->json([
            'status' => 'success',
            'user'   => auth()->user()
        ]);
    }

     // Update perfil
    public function update(Request $request)
    {
        $user = auth()->user();


        try {
            $request->validate([
                'name'     => 'nullable|string|max:100',
                'email'    => 'nullable|string|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6|confirmed',
                'fecha_nacimiento' => 'nullable|date',
                'genero'   => 'nullable|string|in:masculino,femenino,otro',

                'razon_social'       => 'nullable|string|max:150',
                'telefono'           => 'nullable|string',
                'categoria_productos'=> 'nullable|string|max:150',

                'tipo_documento'     => 'nullable|string',
                'numero_documento'   => 'nullable|string',
                'direccion'          => 'nullable|string',

                'numero_cuenta'      => 'nullable|string|max:50',
                'banco'              => 'nullable|string|max:100',
                'tipo_cuenta'        => 'nullable|string|max:100',

                'nombre_tienda'      => 'nullable|string|max:150',
                'politicas_envio'    => 'nullable|string',

                'documento_rut'         => 'nullable|file|mimes:pdf,jpg,png|max:2048',
                'documento_camcomercio' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
                'photo'                 => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        }

        $data = $request->except(['password', 'photo', 'documento_rut', 'documento_camcomercio']);
        if ($request->filled('password')) {
            $data['password'] = \Hash::make($request->password);
        }

        $user->fill($data);

        if ($request->hasFile('photo')) {
            $user->photo = $request->file('photo')->store('photos', 'public');
        }
        if ($request->hasFile('documento_rut')) {
            $user->documento_rut = $request->file('documento_rut')->store('documentos', 'public');
        }
        if ($request->hasFile('documento_camcomercio')) {
            $user->documento_camcomercio = $request->file('documento_camcomercio')->store('documentos', 'public');
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'user'   => $user->fresh()
        ]);
    }


    // Logout
    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Sesión cerrada']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo cerrar sesión'], 500);
        }
    }

    public function usuariosMasivos(Request $request)
    {
        // Espera recibir ?ids[]=1&ids[]=2&ids[]=3 o ids=1,2,3
        $ids = $request->input('ids');

        if (is_string($ids)) {
            $ids = explode(',', $ids); // por si viene en formato ids=1,2,3
        }

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['error' => 'Debes enviar al menos un id'], 400);
        }

        $usuarios = User::whereIn('id', $ids)
            ->get(['id', 'name']); // solo devolvemos id y nombre

        return response()->json($usuarios);
    }

    // Mostrar info de usuario por id
    public function show($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json([
                'mensaje' => 'Usuario no encontrado'
            ], 404);
        }

        // Solo devolver campos básicos
        return response()->json([
            'nombre' => $usuario->name,
            'email' => $usuario->email, 
            'photo' => $usuario->photo,
        ]);
    }
   
}
