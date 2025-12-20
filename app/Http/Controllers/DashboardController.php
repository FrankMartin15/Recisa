<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserSpecialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function dashboard()
   {
       $user = Auth::user();
       
       if ($user->user_level == 1) {
           $doctor = User::where('user_level', 3)->count();
           
           // Conteo de cupos disponibles total (suma de cupos de doctores)
           $quatity = UserSpecialization::sum('cupo_doctor');
           
           // Validación para quatity null
           if (is_null($quatity)) {
               $quatity = 0;
           }
           
           // Total de citas programadas
           $appointment = Appointment::count();
           
           // Capacidad máxima = cupos disponibles + citas ya programadas
           $maxquatity = $quatity + $appointment;
           
           $patient = Patient::count();

           // Para Estado de Doctores
           $doctors = UserSpecialization::with(['user', 'specialization'])
               ->whereHas('user', function ($query) {
                   $query->where('user_level', 3);
               })
               ->get()
               ->map(function ($userSpec) {
                   return (object)[
                       'image' => $userSpec->user->image,
                       'user_status' => $userSpec->user->status,
                       'specialization_name' => $userSpec->specialization->name,
                       'user_name' => $userSpec->user->names . ' ' . $userSpec->user->surnames,
                       'cupo_doctor' => $userSpec->cupo_doctor
                   ];
               });

           // Para Próximas Citas
           $appointments = Appointment::with(['patient', 'doctor.user'])
               ->where('date', '>=', now()->toDateString())
               ->orderBy('date', 'asc')
               ->orderBy('time', 'asc')
               ->take(5)
               ->get();

           return view('admin.dashboard', compact('doctor', 'appointment', 'patient', 'maxquatity', 'doctors', 'appointments'));
       }
       
       if ($user->user_level == 2) {
           $doctor = User::where('user_level', 3)->count();
           $appointment = Appointment::count();
           return view('secretary.dashboard', compact('doctor', 'appointment'));
       }
       
       if ($user->user_level == 3) {
           // Obtener las especializaciones del usuario junto con el conteo de citas
           $assignments = UserSpecialization::where('id_user', $user->id)
               ->with(['specialization'])
               ->withCount([
                   'appointment as appointment_pending_count' => function ($query) {
                       $query->where('status', 0);
                   }
               ])
               ->get();
           // Saber el avance de la atención de citas DEL DÍA DE HOY
           $atendidos = UserSpecialization::where('id_user', $user->id)
               ->with(['specialization'])
               ->withCount([
                   'appointment as appointment_attended_count' => function ($query) {
                       $query->where('status', 1)
                             ->where('date', date('Y-m-d'));
                   },
                   'appointment as appointment_noshow_count' => function ($query) {
                       $query->where('status', 2)
                             ->where('date', date('Y-m-d'));
                   },
                   'appointment as appointment_today_count' => function ($query) {
                       $query->where('date', date('Y-m-d'));
                   }
               ])
               ->get();
           
           // Traer TODAS las citas del doctor para hoy (no solo pendientes)
           $appointments = Appointment::whereHas('doctor', function ($query) use ($user) {
                               $query->where('id_user', $user->id);
                           })
                           ->where('date', date('Y-m-d'))
                           ->orderBy('time', 'asc')
                           ->get();
           
           return view('doctor.dashboard', compact('assignments', 'atendidos', 'appointments'));
       }
   }
}