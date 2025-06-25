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
           //Conteo de citas en espera
           $quatity = DB::table('specializations')
               ->select('quantity_voucher')
               ->unionAll(DB::table('user_specialization')->select('cupo_doctor'))
               ->get()
               ->sum('quantity_voucher');
           //Validadcion para quatity null
           if (is_null($quatity)) {
               $quatity = 0;
           }
           $appointment = Appointment::count();
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
           // Saber el avance de la atención de citas
           $atendidos = UserSpecialization::where('id_user', $user->id)
               ->with(['specialization'])
               ->withCount([
                   'appointment as appointment_pending_count' => function ($query) {
                       $query->where('status', 1);
                   },
                   'appointment as appointment_cancel_count' => function ($query) {
                       $query->where('status', 2);
                   },
                   'appointment as appointment_count'
               ])
               ->get();
           return view('doctor.dashboard', compact('assignments', 'atendidos'));
       }
   }
}