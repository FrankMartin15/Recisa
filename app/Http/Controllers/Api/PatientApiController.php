<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientApiController extends Controller
{
    /**
     * Obtener todos los pacientes
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        // Filtrar por fecha de actualización si se proporciona
        if ($request->has('updated_after')) {
            $query->where('updated_at', '>', $request->updated_after);
        }

        // Incluir relaciones si se solicita
        if ($request->has('with_appointments')) {
            $query->with('appointments');
        }

        if ($request->has('with_histories')) {
            $query->with('clinicalHistories');
        }

        $patients = $query->orderBy('updated_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $patients,
            'count' => $patients->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Obtener un paciente específico
     */
    public function show($id)
    {
        $patient = Patient::with(['appointments', 'clinicalHistories'])->find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Buscar paciente por DNI
     */
    public function findByDni($dni)
    {
        $patient = Patient::where('dni', $dni)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Crear nuevo paciente
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|size:8|unique:patients,dni',
            'names' => 'required|string|max:255',
            'surnames' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'date' => 'required|date',
            'history_number' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generar número de historia si no se proporciona
        $data = $request->all();
        if (empty($data['history_number'])) {
            $data['history_number'] = 'HC-' . str_pad(Patient::count() + 1, 6, '0', STR_PAD_LEFT);
        }

        $patient = Patient::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Paciente creado correctamente',
            'data' => $patient
        ], 201);
    }

    /**
     * Actualizar paciente
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'dni' => 'sometimes|string|size:8|unique:patients,dni,' . $id,
            'names' => 'sometimes|string|max:255',
            'surnames' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'date' => 'sometimes|date',
            'history_number' => 'sometimes|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $patient->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Paciente actualizado correctamente',
            'data' => $patient
        ]);
    }

    /**
     * Eliminar paciente
     */
    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        // Verificar si tiene citas pendientes
        $pendingAppointments = $patient->appointments()->where('status', 0)->count();
        if ($pendingAppointments > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el paciente tiene citas pendientes'
            ], 400);
        }

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paciente eliminado correctamente'
        ]);
    }

    /**
     * Búsqueda de pacientes
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'La búsqueda debe tener al menos 2 caracteres'
            ], 400);
        }

        $patients = Patient::where('dni', 'LIKE', "%{$query}%")
            ->orWhere('names', 'LIKE', "%{$query}%")
            ->orWhere('surnames', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $patients,
            'count' => $patients->count()
        ]);
    }
}
