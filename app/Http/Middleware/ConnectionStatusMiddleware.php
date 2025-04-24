<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class ConnectionStatusMiddleware
{
    public function handle($request, Closure $next)
    {
        // Aquí puedes implementar la lógica para determinar si está online o offline
        $isOnline = $this->checkConnection(); // Implementa esta función según tu lógica

        // Establecer el estado de conexión en la sesión
        session(['connection_status' => $isOnline]);
        // Depurar el valor
        return $next($request);
    }

    private function checkConnection()
    {
        // Ejemplo: Verificar si un servicio externo está disponible
        try {
            $connected = @fsockopen("www.google.com", 80); 
            if ($connected) {
                fclose($connected);
                return true; // Online
            }
        } catch (\Exception $e) {
            return false; // Offline
        }
        return false; // Offline
    }
}