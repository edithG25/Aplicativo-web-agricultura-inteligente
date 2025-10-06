<?php

namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Closure;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user(); 

        if (!$user || !in_array($user->role, $roles)) {
            return response()->json(['error' => 'Forbidden - Insufficient role'], 403);
        }

        return $next($request);
    }
}



