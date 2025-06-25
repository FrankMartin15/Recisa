<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConnectionStatusMiddleware
{
    public function handle($request, Closure $next)
    {
        $isOnline = $this->checkConnection();

        // Establecer el estado de conexión en la sesión
        session(['connection_status' => $isOnline]);
        
        // Pasar a todas las vistas
        view()->share('serverOnline', $isOnline);
        
        // Log para debugging
        Log::info('Connection Status:', ['online' => $isOnline]);
        
        return $next($request);
    }

    private function checkConnection()
    {
        try {
            // 1. Verificar conexión a base de datos
            DB::connection()->getPdo();
            
            // 2. Verificar conexión a internet (opcional)
            $connected = @fsockopen("8.8.8.8", 53, $errno, $errstr, 3);
            if ($connected) {
                fclose($connected);
                return true;
            }
            
            // Si la BD funciona pero no hay internet, aún consideramos online
            return true;
            
        } catch (\Exception $e) {
            Log::warning('Connection check failed:', ['error' => $e->getMessage()]);
            return false;
        }
    }
}