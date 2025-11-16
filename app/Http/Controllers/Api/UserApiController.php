<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Specialization;
use App\Models\UserSpecialization;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * Obtener todos los usuarios
     */
    public function index(Request $request)
    {
        $query = User::with(['userGroup', 'specializations.specialization']);

        // Filtrar por fecha de actualización
        if ($request->has('updated_after')) {
            $query->where('updated_at', '>', $request->updated_after);
        }

        // Filtrar por nivel de usuario
        if ($request->has('user_level')) {
            $query->where('user_level', $request->user_level);
        }

        // Solo activos
        if ($request->has('active_only') && $request->active_only) {
            $query->where('status', 1);
        }

        $users = $query->orderBy('names', 'asc')->get();

        // Ocultar passwords por seguridad
        $users->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Obtener un usuario específico
     */
    public function show($id)
    {
        $user = User::with(['userGroup', 'specializations.specialization'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Obtener solo doctores
     */
    public function doctors(Request $request)
    {
        $query = User::with(['specializations.specialization'])
            ->where('user_level', 3) // 3 = Doctor
            ->where('status', 1); // Solo activos

        if ($request->has('updated_after')) {
            $query->where('updated_at', '>', $request->updated_after);
        }

        $doctors = $query->orderBy('names', 'asc')->get();
        $doctors->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'data' => $doctors,
            'count' => $doctors->count()
        ]);
    }

    /**
     * Obtener especializaciones
     */
    public function specializations(Request $request)
    {
        $query = Specialization::query();

        if ($request->has('updated_after')) {
            $query->where('updated_at', '>', $request->updated_after);
        }

        $specializations = $query->orderBy('title', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $specializations,
            'count' => $specializations->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Obtener asignaciones de usuario-especialización
     */
    public function userSpecializations(Request $request)
    {
        $query = UserSpecialization::with(['user', 'specialization']);

        if ($request->has('updated_after')) {
            $query->where('updated_at', '>', $request->updated_after);
        }

        if ($request->has('user_id')) {
            $query->where('id_user', $request->user_id);
        }

        $userSpecs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $userSpecs,
            'count' => $userSpecs->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Obtener grupos de usuario
     */
    public function userGroups()
    {
        $groups = UserGroup::orderBy('group_level', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 1)->count(),
            'admins' => User::where('user_level', 1)->count(),
            'secretaries' => User::where('user_level', 2)->count(),
            'doctors' => User::where('user_level', 3)->count(),
            'total_specializations' => Specialization::count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Obtener perfil del usuario actual (basado en sesión)
     */
    public function me(Request $request)
    {
        $userId = session('usuario_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        $user = User::with(['userGroup', 'specializations.specialization'])->find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}
