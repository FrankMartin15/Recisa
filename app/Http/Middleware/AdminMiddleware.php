<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if(!Auth::check()){
            return redirect(url('/login'));
        }

        $user = Auth::user();

        // Verificar que el usuario y su grupo existan y estén activos
        if($user->status == 1 && $user->group && $user->group->group_status == 1){
            if($user->user_level == 1){
                return $next($request);
            }else{  
                // No cerrar sesión, solo redirigir
                return redirect(url('/401'))->with('error', 'No tienes permisos de administrador');
            } 
        }else{
            // No cerrar sesión, solo redirigir
            return redirect(url('/401'))->with('error', 'Tu cuenta no está activa');
        }
    }
}
