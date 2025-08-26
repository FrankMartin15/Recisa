<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApointment; // Asegúrate que el namespace sea correcto
use App\Models\Appointment;
use App\Models\ClinicalHistories;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserSpecialization;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException; // Para capturar errores de validación

class AppointmentController extends Controller
{
    public function list(){
        // ... tu lógica existente ...
        $appointments=Appointment::all();
        return view('appointments.list',compact('appointments'));
    }

    public function add(){
        // ... tu lógica existente ...
        $quotas = UserSpecialization::with(['user', 'specialization'])->get();
        $patients= Patient::all();
        $today=date('Y-m-d');
        $hour=Appointment::where('date', $today)->get(); // Esto obtiene todas las citas de hoy, no solo las horas.
                                                     // Considera: $hour = Appointment::where('date', $today)->pluck('time')->toArray();
                                                     // Y luego en la vista `var reservedHours = @json($hour);` sería un array de strings.
        $doctors=User::with(['specializations.specialization.userSpecializations.appointment' => function($query) use ($today) {
            $query->where('date', $today)->where('status', 0); // Filtra las citas aquí para el modal
        }])
                      ->whereHas('specializations') // Asegura que solo doctores con especialidades sean listados
                      ->where('user_level',3)->get();
        return view('appointments.created',compact('quotas','patients','today','doctors','hour'));
    }

    public function insert(StoreApointment $request){ // StoreApointment ya maneja la validación
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
            } else {
                // Opcional: Manejar caso donde el cupo ya era 0 (podría pasar si hubo un submit offline y otro online casi al mismo tiempo)
                // Por ahora, la validación en StoreApointment debería prevenir esto en la mayoría de los casos si se actualiza la UI.
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
        // ... tu lógica existente ...
        $clinical_histories = ClinicalHistories::where('id_patient', $appointment->patient->id)->get();
        $birthDate = Carbon::parse($appointment->patient->date);
        $currentDate = Carbon::now();
        $age = $currentDate->diffInYears($birthDate);
        return view('appointments.show',compact('appointment','clinical_histories', 'age'));
    }
}