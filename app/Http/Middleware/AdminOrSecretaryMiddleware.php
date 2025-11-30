<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOrSecretaryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::check()){
            return redirect(url('/login'));
        }

        $user = Auth::user();

        if($user->status == 1 && $user->group && $user->group->group_status == 1){
            if($user->user_level == 1 || $user->user_level == 2){
                return $next($request);
            }else{  
                return redirect(url('/401'))->with('error', 'No tienes permisos suficientes');
            } 
        }else{
            return redirect(url('/401'))->with('error', 'Tu cuenta no está activa');
        }
    }
}
