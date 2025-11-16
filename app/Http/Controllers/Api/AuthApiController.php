<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    /**
     * Login y generar token para uso offline
     */
    public function login(Request $request)
    {
        $request->validate([
            'dni' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::with(['userGroup', 'specializations.specialization'])
            ->where('dni', $request->dni)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 401);
        }

        // Verificar contraseña usando bcrypt (Hash::check)
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        // Verificar que el usuario esté activo
        if ($user->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario inactivo'
            ], 403);
        }

        // Generar token para offline
        $token = Str::random(64);
        $user->api_token = $token;
        $user->token_expires_at = now()->addDays(30); // Token válido por 30 días
        $user->save();

        // Iniciar sesión web también
        session([
            'usuario_id' => $user->id,
            'user_level' => $user->user_level,
            'logueado' => true
        ]);

        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $user,
                'token' => $token,
                'expires_at' => $user->token_expires_at
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            $user = User::where('api_token', $token)->first();
            if ($user) {
                $user->api_token = null;
                $user->token_expires_at = null;
                $user->save();
            }
        }

        // Limpiar sesión web
        session()->flush();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * Verificar si el token es válido
     */
    public function verify(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }

        $user = User::where('api_token', $token)
            ->where('token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido o expirado'
            ], 401);
        }

        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'message' => 'Token válido',
            'data' => [
                'user' => $user,
                'expires_at' => $user->token_expires_at
            ]
        ]);
    }

    /**
     * Renovar token
     */
    public function refresh(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
        }

        // Generar nuevo token
        $newToken = Str::random(64);
        $user->api_token = $newToken;
        $user->token_expires_at = now()->addDays(30);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Token renovado',
            'data' => [
                'token' => $newToken,
                'expires_at' => $user->token_expires_at
            ]
        ]);
    }

    /**
     * Obtener datos iniciales para sincronización offline
     */
    public function initialData(Request $request)
    {
        $userId = session('usuario_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        $user = User::with(['userGroup', 'specializations.specialization'])->find($userId);
        $user->makeHidden(['password']);

        // Cargar todos los datos necesarios para offline
        $data = [
            'user' => $user,
            'patients' => \App\Models\Patient::all(),
            'appointments' => \App\Models\Appointment::with(['patient', 'doctor.user', 'doctor.specialization'])->get(),
            'users' => User::with(['userGroup'])->get()->makeHidden(['password']),
            'specializations' => \App\Models\Specialization::all(),
            'user_specializations' => \App\Models\UserSpecialization::with(['user', 'specialization'])->get(),
            'user_groups' => \App\Models\UserGroup::all()
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ]);
    }
}
