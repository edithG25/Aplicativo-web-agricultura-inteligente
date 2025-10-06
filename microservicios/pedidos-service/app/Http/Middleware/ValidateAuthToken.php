<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class ValidateAuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        // Llamada al microservicio auth
        $response = Http::withToken($token)->get(env('AUTH_SERVICE_URL').'/api/validate');

        if ($response->failed() || !$response->json('valid')) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        // Inyectamos el usuario validado en el request
        $userData = $response->json('user');

        $request->setUserResolver(function () use ($userData) {
            return (object) $userData; // lo devuelves como objeto para que funcione $user->role
        });

        return $next($request);
    }
}
