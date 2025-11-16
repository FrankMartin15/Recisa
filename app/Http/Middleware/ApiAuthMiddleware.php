<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Verifica autenticación por token Bearer o sesión web
     */
    public function handle(Request $request, Closure $next)
    {
        // Primero intentar con Bearer token
        $token = $request->bearerToken();

        if ($token) {
            $user = User::where('api_token', $token)
                ->where('token_expires_at', '>', now())
                ->first();

            if ($user) {
                // Token válido, continuar
                $request->merge(['auth_user' => $user]);
                return $next($request);
            }
        }

        // Si no hay token, verificar sesión web
        if (session('logueado') && session('usuario_id')) {
            $user = User::find(session('usuario_id'));
            if ($user) {
                $request->merge(['auth_user' => $user]);
                return $next($request);
            }
        }

        // No autenticado
        return response()->json([
            'success' => false,
            'message' => 'No autorizado. Debe iniciar sesión.'
        ], 401);
    }
}
