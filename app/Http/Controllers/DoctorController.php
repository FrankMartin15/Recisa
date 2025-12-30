<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalHistories;
use App\Models\UserSpecialization;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function index(){
        $user= Auth::user();  
        // Saber el avance de la atención de citas
        $atendidos = UserSpecialization::where('id_user', $user->id)
                        ->with(['specialization'])
                        ->withCount([
                            'appointment as appointment_pending_count' => function ($query) {
                                $query->where('status', 1)
                                      ->where('date',date('Y-m-d'));
                            },
                            'appointment as appointment_cancel_count' => function ($query) {
                                $query->where('status', 2)
                                      ->where('date',date('Y-m-d'));
                            },
                            'appointment as appointment_count'
                        ])
                        ->get();    
        //Traer a todos los pacientes por atender
        $appointments = Appointment::whereHas('doctor', function ($query) use ($user) {
                            $query->where('id_user', $user->id);
                        })
                        ->where('status', 0)
                        ->where('date',date('Y-m-d'))
                        ->get();
        return view('doctor.citas.index',compact('atendidos','appointments'));
    }

    // API JSON para IndexedDB offline
    public function listJson(){
        $user= Auth::user();  
        
        // Datos de avance
        $atendidos = UserSpecialization::where('id_user', $user->id)
                        ->with(['specialization'])
                        ->withCount([
                            'appointment as appointment_pending_count' => function ($query) {
                                $query->where('status', 1)
                                      ->where('date',date('Y-m-d'));
                            },
                            'appointment as appointment_cancel_count' => function ($query) {
                                $query->where('status', 2)
                                      ->where('date',date('Y-m-d'));
                            },
                            'appointment as appointment_count'
                        ])
                        ->get();    
        
        // Citas pendientes con relaciones necesarias
        $appointments = Appointment::whereHas('doctor', function ($query) use ($user) {
                            $query->where('id_user', $user->id);
                        })
                        ->with(['patient.clinicalHistories', 'doctor.specialization'])
                        ->where('status', 0)
                        ->where('date',date('Y-m-d'))
                        ->get();
        
        // Append full URL for PDFs to ensure consistency with Blade
        $appointments->each(function($appt) {
            if($appt->patient && $appt->patient->clinicalHistories) {
                $appt->patient->clinicalHistories->each(function($hist) {
                    $hist->full_url = \Illuminate\Support\Facades\Storage::url($hist->source_pdf);
                });
            }
        });
        
        return response()->json([
            'success' => true,
            'atendidos' => $atendidos,
            'appointments' => $appointments,
            'cached_at' => now()->toIso8601String()
        ]);
    }
    public function edit(Appointment $appointment){
        $clinical_histories = ClinicalHistories::where('id_patient', $appointment->patient->id)->get();
        $birthDate = Carbon::parse($appointment->patient->age);
        $currentDate = Carbon::now();
        $age = $currentDate->diffInYears($birthDate);
        return view('doctor.citas.show', compact('appointment','clinical_histories', 'age'));
    }
    public function update(Request $request,$id){
        // Normalizar hora: aceptar "HH:MM" o "HH:MM:SS" y convertir a "HH:MM"
        if ($request->has('time')) {
            $rawTime = (string) $request->input('time');
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $rawTime)) {
                $request->merge(['time' => substr($rawTime, 0, 5)]);
            }
        }
        $validator = Validator::make($request->all(), [
            'date' => ['required','date_format:Y-m-d'],
            'time' => ['required','date_format:H:i'],
            'status' => 'required|in:0,1,2',
            'description' => 'nullable',
        ],[], [
            'date' => 'fecha',
            'time' => 'hora',
            'status' => 'estado' 
        ]);
        $validator->sometimes('description', 'required', function ($input) {
            return $input->status == 2;
        });  
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        // Encuentra la cita y actualiza los datos
        $appointment = Appointment::findOrFail($id);
        $appointment->date = $request->input('date');
        $appointment->time = $request->input('time');
        $appointment->status = $request->input('status');
        $appointment->description = $request->input('description');
        $appointment->save();

        return redirect()->back()->with('success', 'Cita actualizada correctamente');
    }

    public function syncOfflineAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|digits:8',
            'status' => 'required|in:1,2',
            'doctor_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $dni = $request->input('dni');
        $status = $request->input('status');
        $doctorId = $request->input('doctor_id');

        // Buscar paciente por DNI
        $patient = \App\Models\Patient::where('dni', $dni)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado con DNI: ' . $dni
            ], 404);
        }

        // Buscar cita del día para este doctor y paciente
        $appointment = Appointment::whereHas('doctor', function($q) use ($doctorId) {
                $q->where('id_user', $doctorId);
            })
            ->where('id_patient', $patient->id)
            ->whereDate('date', date('Y-m-d'))
            ->where('status', 0) // Solo citas pendientes
            ->first();

        if ($appointment) {
            // Actualizar cita existente
            $appointment->status = $status;
            $appointment->save();

            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada correctamente',
                'appointment_id' => $appointment->id
            ]);
        } else {
            // No hay cita programada para hoy
            // Registrar en log para auditoría (opcional)
            \Log::info("Asistencia offline sin cita previa: DNI {$dni}, Doctor {$doctorId}");

            return response()->json([
                'success' => false,
                'message' => 'No hay cita programada para este paciente hoy',
                'dni' => $dni
            ], 404);
        }
    }

    // Método para filtrar citas por rango de fechas
    public function filtrarCitas(Request $request)
    {
        $user = Auth::user();
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        
        $appointments = Appointment::whereHas('doctor', function ($query) use ($user) {
                            $query->where('id_user', $user->id);
                        })
                        ->whereBetween('date', [$fechaInicio, $fechaFin])
                        ->with(['patient', 'doctor.specialization'])
                        ->orderBy('date', 'asc')
                        ->orderBy('time', 'asc')
                        ->get();
        
        return response()->json([
            'success' => true,
            'appointments' => $appointments
        ]);
    }

}
