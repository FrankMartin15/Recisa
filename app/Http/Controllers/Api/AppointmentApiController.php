<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\UserSpecialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentApiController extends Controller
{
    /**
     * Obtener todas las citas
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor.user', 'doctor.specialization']);

        // Filtrar por fecha de actualización
        if ($request->has('updated_after')) {
            $query->where('updated_at', '>', $request->updated_after);
        }

        // Filtrar por fecha específica
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filtrar por paciente
        if ($request->has('patient_id')) {
            $query->where('id_patient', $request->patient_id);
        }

        // Filtrar por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrar por doctor
        if ($request->has('doctor_id')) {
            $query->where('id_quota', $request->doctor_id);
        }

        $appointments = $query->orderBy('date', 'desc')
            ->orderBy('time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments,
            'count' => $appointments->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Obtener una cita específica
     */
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor.user', 'doctor.specialization'])->find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $appointment
        ]);
    }

    /**
     * Crear nueva cita
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_quota' => 'required|exists:user_specialization,id',
            'id_patient' => 'required|exists:patients,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|integer|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar disponibilidad del doctor
        $existingAppointment = Appointment::where('id_quota', $request->id_quota)
            ->where('date', $request->date)
            ->where('time', $request->time)
            ->where('status', '!=', 2) // No cancelada
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'success' => false,
                'message' => 'El doctor ya tiene una cita en ese horario'
            ], 400);
        }

        // Verificar cupo del doctor
        $doctorSpec = UserSpecialization::find($request->id_quota);
        if (!$doctorSpec) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor no encontrado'
            ], 404);
        }

        $appointmentsToday = Appointment::where('id_quota', $request->id_quota)
            ->where('date', $request->date)
            ->where('status', '!=', 2)
            ->count();

        if ($appointmentsToday >= $doctorSpec->cupo_doctor) {
            return response()->json([
                'success' => false,
                'message' => 'El doctor ha alcanzado el límite de citas para este día'
            ], 400);
        }

        $data = $request->all();
        $data['status'] = $data['status'] ?? 0; // 0 = pendiente por defecto

        $appointment = Appointment::create($data);
        $appointment->load(['patient', 'doctor.user', 'doctor.specialization']);

        return response()->json([
            'success' => true,
            'message' => 'Cita creada correctamente',
            'data' => $appointment
        ], 201);
    }

    /**
     * Actualizar cita
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_quota' => 'sometimes|exists:user_specialization,id',
            'id_patient' => 'sometimes|exists:patients,id',
            'date' => 'sometimes|date',
            'time' => 'sometimes|date_format:H:i',
            'description' => 'sometimes|string|max:1000',
            'status' => 'sometimes|integer|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $appointment->update($request->all());
        $appointment->load(['patient', 'doctor.user', 'doctor.specialization']);

        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada correctamente',
            'data' => $appointment
        ]);
    }

    /**
     * Eliminar/Cancelar cita
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }

        // En lugar de eliminar, marcar como cancelada
        $appointment->status = 2; // 2 = cancelada
        $appointment->save();

        return response()->json([
            'success' => true,
            'message' => 'Cita cancelada correctamente'
        ]);
    }

    /**
     * Obtener citas de hoy
     */
    public function today()
    {
        $appointments = Appointment::with(['patient', 'doctor.user', 'doctor.specialization'])
            ->whereDate('date', now()->toDateString())
            ->orderBy('time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments,
            'count' => $appointments->count()
        ]);
    }

    /**
     * Obtener estadísticas de citas
     */
    public function stats()
    {
        $today = now()->toDateString();

        $stats = [
            'total' => Appointment::count(),
            'today' => Appointment::whereDate('date', $today)->count(),
            'pending' => Appointment::where('status', 0)->count(),
            'attended' => Appointment::where('status', 1)->count(),
            'cancelled' => Appointment::where('status', 2)->count(),
            'today_pending' => Appointment::whereDate('date', $today)->where('status', 0)->count(),
            'today_attended' => Appointment::whereDate('date', $today)->where('status', 1)->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
