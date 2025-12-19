<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApointment; // Asegúrate que el namespace sea correcto
use App\Models\Appointment;
use App\Models\ClinicalHistories;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use App\Models\UserSpecialization;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException; // Para capturar errores de validación

class AppointmentController extends Controller
{
    public function list(){
        $user = Auth::user();

        $query = Appointment::with([
            'patient',
            'doctor.user',
            'doctor.specialization',
        ]);

        // Si es doctor, solo listar citas correspondientes a sus especialidades (id_user en user_specialization)
        if ($user && (int) $user->user_level === 3) {
            $query->whereHas('doctor', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            });
        }

        $appointments = $query->get();

        // Datos para el modal de reportes (según rol)
        if ($user && (int) $user->user_level === 3) {
            $filterDoctors = User::where('id', $user->id)->get();
            $filterSpecializations = Specialization::whereHas('userSpecializations', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            })->orderBy('name')->get();
        } else {
            $filterDoctors = User::where('user_level', 3)->orderBy('surnames')->orderBy('names')->get();
            $filterSpecializations = Specialization::orderBy('name')->get();
        }

        $allowedPatientIds = (clone $query)->select('id_patient')->distinct()->pluck('id_patient');
        $filterPatients = Patient::whereIn('id', $allowedPatientIds)->orderBy('surnames')->orderBy('names')->get();

        return view('appointments.list', compact('appointments', 'filterDoctors', 'filterSpecializations', 'filterPatients'));
    }

    public function reporte(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'id_specialization' => ['nullable', 'integer', 'exists:specializations,id'],
            'id_doctor' => ['nullable', 'integer', 'exists:users,id'],
            'id_patient' => ['nullable', 'integer', 'exists:patients,id'],
        ]);

        $user = Auth::user();

        $query = Appointment::with([
            'patient',
            'doctor.user',
            'doctor.specialization',
        ])->whereBetween('date', [$validated['start_date'], $validated['end_date']]);

        // Filtros opcionales
        if (!empty($validated['id_patient'])) {
            $query->where('id_patient', $validated['id_patient']);
        }
        if (!empty($validated['id_doctor'])) {
            $doctorId = (int) $validated['id_doctor'];
            $query->whereHas('doctor', function ($q) use ($doctorId) {
                $q->where('id_user', $doctorId);
            });
        }
        if (!empty($validated['id_specialization'])) {
            $specializationId = (int) $validated['id_specialization'];
            $query->whereHas('doctor', function ($q) use ($specializationId) {
                $q->where('id_specialization', $specializationId);
            });
        }

        // Si es doctor, nunca permitir salir de sus propias citas
        if ($user && (int) $user->user_level === 3) {
            $query->whereHas('doctor', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            });
        }

        $appointments = $query->orderBy('date')->orderBy('time')->get();

        $filterDoctor = !empty($validated['id_doctor']) ? User::find($validated['id_doctor']) : null;
        $filterSpecialization = !empty($validated['id_specialization']) ? Specialization::find($validated['id_specialization']) : null;
        $filterPatient = !empty($validated['id_patient']) ? Patient::find($validated['id_patient']) : null;

        $view = View::make('report.appointment_report', [
            'appointments' => $appointments,
            'filters' => $validated,
            'filterDoctor' => $filterDoctor,
            'filterSpecialization' => $filterSpecialization,
            'filterPatient' => $filterPatient,
        ]);
        $html = $view->render();

        $mpdf = $this->createSafeMpdf();
        $footerHtml = '<footer>Página {PAGENO} de {nbpg}</footer>';
        $mpdf->SetHTMLFooter($footerHtml);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('reporte_citas.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }

    public function listJson(){
        $user = Auth::user();

        $query = Appointment::with([
            'patient',
            'doctor.user',
            'doctor.specialization',
        ]);

        if ($user && (int) $user->user_level === 3) {
            $query->whereHas('doctor', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            });
        }

        return response()->json([
            'success' => true,
            'appointments' => $query->get(),
        ]);
    }

    public function add(){
        // ... tu lógica existente ...
        $quotas = UserSpecialization::with(['user', 'specialization'])->get();
        $patients= Patient::all();
        $today=date('Y-m-d');
        $hour=Appointment::where('date', $today)->get(); // Esto obtiene todas las citas de hoy, no solo las horas.
                                                     // Considera: $hour = Appointment::where('date', $today)->pluck('time')->toArray();
                                                     // Y luego en la vista `var reservedHours = @json($hour);` sería un array de strings.

        $authUser = Auth::user();

        // Doctores + especialidades + citas de HOY (para el modal "Ver Citas")
        $doctorsQuery = User::query()
            ->where('user_level', 3)
            ->whereHas('specializations')
            ->with([
                'specializations.specialization',
                'specializations.appointment' => function ($query) use ($today) {
                    $query->where('date', $today)
                        ->where('status', 0)
                        ->with('patient');
                }
            ]);

        // Si es doctor, solo verse a sí mismo en la tabla del día
        if ($authUser && (int) $authUser->user_level === 3) {
            $doctorsQuery->where('id', $authUser->id);
        }

        $doctors = $doctorsQuery->get();
        return view('appointments.created',compact('quotas','patients','today','doctors','hour'));
    }

    public function insert(Request $request){
        // --- LÓGICA DE SINCRONIZACIÓN OFFLINE ---
        if ($request->has('_offline_sync')) {
            try {
                Log::info('🔧 Cita recibida para sincronización offline:', $request->all());

                // Validación manual simplificada para offline
                $validated = $request->validate([
                    'id_quota' => 'required|integer|exists:user_specialization,id',
                    'id_patient' => 'required|integer|exists:patients,id',
                    'date' => [
                        'required',
                        'date',
                        function ($attribute, $value, $fail) {
                            try {
                                if (Carbon::parse($value)->isWeekend()) {
                                    $fail('Solo se permite seleccionar fechas de lunes a viernes.');
                                }
                            } catch (\Exception $e) {
                                $fail('La fecha ingresada no es válida.');
                            }
                        },
                    ],
                    'time' => 'required', // No validamos formato estricto ni duplicados aquí
                ]);

                DB::beginTransaction();

                // Verificar duplicados manualmente para evitar error 500 si ya existe
                $exists = Appointment::where('id_quota', $request->id_quota)
                    ->where('date', $request->date)
                    ->where('time', $request->time)
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return response()->json([
                        'success' => true, // Retornamos true para que el cliente borre la petición de la cola
                        'message' => 'La cita ya existía (sincronizada previamente).',
                        'was_duplicate' => true
                    ]);
                }

                $appointment = new Appointment();
                $appointment->fill([
                     'id_quota' => $request->id_quota,
                     'id_patient' => $request->id_patient,
                     'date' => $request->date,
                     'time' => $request->time,
                     'description' => $request->description ?? null,
                     'status' => 0
                ]);
                $appointment->save();

                // Actualizar cupos
                $userSpecialization = UserSpecialization::findOrFail($request->id_quota);
                if ($userSpecialization->cupo_doctor > 0) {
                    $userSpecialization->cupo_doctor -= 1;
                    $userSpecialization->save();
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Cita sincronizada correctamente.',
                    'appointment_id' => $appointment->id
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Error sincronizando cita:', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
            }
        }

        // --- LÓGICA NORMAL (ONLINE) ---
        // Usamos validación manual aquí también para reemplazar StoreApointment
        $request->validate([
            'id_quota' => [
                'required',
                'integer',
                'exists:user_specialization,id',
                function ($attribute, $value, $fail) use ($request) {
                    $appointment = Appointment::where('id_quota', $value)
                        ->where('date', $request->date)
                        ->where('time', $request->time)
                        ->first();
                    if ($appointment) {
                        $fail('Ya existe una cita para este horario.');
                    }
                },
            ],
            'id_patient' => 'required|integer|exists:patients,id',
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    try {
                        if (Carbon::parse($value)->isWeekend()) {
                            $fail('Solo se permite seleccionar fechas de lunes a viernes.');
                        }
                    } catch (\Exception $e) {
                        $fail('La fecha ingresada no es válida.');
                    }
                },
            ],
            'time' => 'required|date_format:H:i',
        ]);

        try {
            DB::beginTransaction();
            
            $appointment=new Appointment();
            $appointment->fill([
                 'id_quota'=>$request->id_quota,
                 'id_patient'=>$request->id_patient,
                 'date'=>$request->date,
                 'time'=>$request->time,
                 'description'=> $request->description ?? null, // Si tienes campo descripción
                 'status'=>0
            ]);
            $appointment->save();

            // Actualizar cantidad de cupos en la tabla user_specialization
            $userSpecialization = UserSpecialization::findOrFail($request->id_quota);
            if ($userSpecialization->cupo_doctor > 0) { // Solo decrementa si hay cupos
                $userSpecialization->cupo_doctor -= 1;
                $userSpecialization->save();
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Cita registrada correctamente.',
                    'appointment_id' => $appointment->id
                ]);
            }
            // El redirect original solo para envíos de formulario no-AJAX (si los hubiera)
            return redirect('recisa/appointments/list')->with('success','Cita registrada correctamente'); 

        } catch (ValidationException $e) { // Errores de validación específicos
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación.',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error al registrar la cita: ' . $e->getMessage()
                ], 500); // Error genérico del servidor
            }
            return redirect()->back()->with('error','Error al registrar la cita: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Appointment $appointment){
        $user = Auth::user();

        // Si es doctor, impedir ver citas que no le pertenecen
        if ($user && (int) $user->user_level === 3) {
            $appointment->loadMissing(['doctor']);
            if (!$appointment->doctor || (int) $appointment->doctor->id_user !== (int) $user->id) {
                return redirect(url('/401'))->with('error', 'No tienes permisos suficientes');
            }
        }

        $clinical_histories = ClinicalHistories::where('id_patient', $appointment->patient->id)->get();
        $birthDate = Carbon::parse($appointment->patient->date);
        $currentDate = Carbon::now();
        $age = $currentDate->diffInYears($birthDate);
        return view('appointments.show',compact('appointment','clinical_histories', 'age'));
    }
}