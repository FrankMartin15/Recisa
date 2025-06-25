<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicalHistoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DNIController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\UserSpecializationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// 🔧 RUTAS MEJORADAS PARA FUNCIONALIDAD OFFLINE/INDEXEDDB
Route::get('/connectivity-check', function () {
    return response()->json([
        'status' => 'online',
        'server_time' => now()->toISOString(),
        'connection' => 'active'
    ]);
})->name('connectivity.check');

Route::post('/update-connection-status', function (Illuminate\Http\Request $request) {
    $status = $request->input('status', true);
    session(['connection_status' => $status]);
    return response()->json(['message' => 'Estado actualizado']);
})->name('connection.status.update');

Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.token');

//La vista de log
Route::get('/', [AuthController::class, 'login']);
//Evitar los datos del login
Route::post('login', [AuthController::class, 'AuthLogin']);
//Cerrar sesion
Route::get('logout', [AuthController::class, 'Logout']);

//Manejo del error en el sistema
//Error si no hay un dato
Route::get('/404', function () {
    return view('page.404');
});
//Usuario no tiene acceso
Route::get('/401', function () {
    return view('page.401');
});
//Usuario no tiene acceso
Route::get('/500', function () {
    return view('page.500');
});
Route::get('/401', function () {
    $specializations = collect(); // ← AGREGAR ESTA LÍNEA
    return view('page.401', compact('specializations'));
});

//Creamos las rutas de los roles
Route::group(['middleware' => 'admin'], function () {
    //Rutas para el rol de admin
    //La vista del dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'dashboard']);
    //La vista de los usuarios
    Route::get('/admin/admin/list', [AdminController::class, 'list']);
    //La vista crear usuario
    Route::get('/admin/admin/add', [AdminController::class, 'add']);
    //Validación de la API
    Route::post('/admin/admin/add-consulta', [DNIController::class, 'consultarDNI']);
    //Envio de datos para registrar
    Route::post('/admin/admin/add', [AdminController::class, 'insert']);
    //Vista editar
    Route::get('admin/admin/edit/{slug}', [AdminController::class, 'edit']);
    //Envio de datos para el edit
    Route::post('admin/admin/edit/{slug}', [AdminController::class, 'update']);
    //Envio de las foto de perfil
    Route::post('admin/admin/edit/photo/{slug}', [AdminController::class, 'photo']);
    //delete get
    Route::get('admin/admin/delete/{id}', [AdminController::class, 'delete']);
    //Generar Reporte de Usuarios
    Route::get('admin/admin/reporte', [AdminController::class, 'reporte']);

    //Rutas para crear los grupos
    // Listar todos los roles
    Route::get('/admin/rol/list', [UserGroupController::class, 'list'])->name('admin.rol.list');
    // Crear nuevo rol
    Route::get('/admin/rol/add', [UserGroupController::class, 'add'])->name('admin.rol.add');
    Route::post('/admin/rol/store', [UserGroupController::class, 'insert'])->name('admin.rol.store');
    // Editar rol existente
    Route::get('/admin/rol/edit/{usergroup}', [UserGroupController::class, 'edit'])->name('admin.rol.edit');
    Route::post('/admin/rol/update/{usergroup}', [UserGroupController::class, 'update'])->name('admin.rol.update');
    // Eliminar rol
    Route::get('/admin/rol/delete/{id}', [UserGroupController::class, 'delete'])->name('admin.rol.delete');

    //Rutas para crear las especialidades
    //La vista de los usuarios
    Route::get('/admin/specialization', [SpecializationController::class, 'list']);
    //Envio de datos para registrar
    Route::post('/admin/specialization', [SpecializationController::class, 'insert']);
    //Envio de datos para el edit
    Route::post('admin/specialization/edit/{id}', [SpecializationController::class, 'update']);
    //delete get
    Route::get('admin/specialization/delete/{id}', [SpecializationController::class, 'delete']);

    //Rutas para asignar los doctores a especialidades
    //La vista de la asignación a doctor
    Route::get('/admin/assignment', [UserSpecializationController::class, 'list']);
    //Envio de datos para registrar
    Route::post('/admin/assignment', [UserSpecializationController::class, 'insert']);
    //delete get
    Route::get('admin/assignment/delete/{id}', [UserSpecializationController::class, 'delete']);
});

Route::group(['middleware' => 'secretary'], function () {
    //La vista del dashboard
    Route::get('secretary/dashboard', [DashboardController::class, 'dashboard']);
    //Reporte de Doctores
    Route::get('secretary/reporte/cita', [SecretaryController::class, 'list']);
});

Route::group(['middleware' => 'doctor'], function () {
    //La vista del dashboard
    Route::get('doctor/dashboard', [DashboardController::class, 'dashboard']);
    Route::get('doctor/citas/list', [DoctorController::class, 'index']);
    Route::get('doctor/attend/edit/{appointment}', [DoctorController::class, 'edit']);
    Route::post('doctor/attend/edit/{appointment}', [DoctorController::class, 'update']);
});

//Admin y la secretaria comparten las rutas para poder generar el proceso de citas
Route::group(['middleware' => 'admin_or_secretary'], function () {
    //Rutas para crear los pacientes
    //La vista de los pacientes
    Route::get('/recisa/patients/list', [PatientController::class, 'list']);
    Route::get('/recisa/patients/add', [PatientController::class, 'add']);
    Route::post('/recisa/patients/add-consulta', [DNIController::class, 'consultarDNI']);
    Route::post('/recisa/patients/add', [PatientController::class, 'insert']);
    Route::get('/recisa/patients/edit/{slug}', [PatientController::class, 'edit']);
    Route::post('/recisa/patients/edit/{slug}', [PatientController::class, 'update']);
    Route::get('/recisa/patients/delete/{id}', [PatientController::class, 'delete']);
    //Buscar el paciente
    Route::post('/recisa/clinicalhistories/sheare-patient', [ClinicalHistoryController::class, 'shearePatient']);

    //Rutas para el historial clinico
    // [PatientController::class, 'updateHistory'])->name('patients.history.update');
    // Route::post('/recisa/patients/history/{id}', [PatientController::class, 'updateHistory'])->name('patients.history.update');
    // Route::get('/recisa/patients/get-files/{id}', [PatientController::class, 'getPatientFiles'])->name('patients.files.get');
    // Route::delete('/recisa/files/delete/{id}', [PatientController::class, 'deleteFile'])->name('patients.file.delete');
    Route::post('/recisa/patients/history/{id}', [PatientController::class, 'updateHistory'])->name('patients.history.update');
    Route::get('/recisa/patients/get-files/{id}', [PatientController::class, 'getPatientFiles'])->name('patients.files.get');
    Route::post('/recisa/files/delete/{id}', [PatientController::class, 'deleteFile'])->name('patients.file.delete');

    // 🔧 RUTAS DE CITAS - MANTENIENDO TU ESTRUCTURA ACTUAL PERO CORRIGIENDO DUPLICACIONES

    // 📋 RUTAS PRINCIPALES DE CITAS (con el typo original que ya tienes funcionando)
    Route::get('/recisa/appoitnment/list', [AppointmentController::class, 'list'])->name('appointments.list.legacy');
    Route::get('/recisa/appoitnment/add', [AppointmentController::class, 'add'])->name('appointments.create.legacy');
    Route::post('/recisa/appoitnment/add', [AppointmentController::class, 'insert'])->name('appointments.store.legacy');
    Route::get('/recisa/appoitnment/show/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show.legacy');

    // 🔧 RUTAS CORREGIDAS DE CITAS (sin typo) - ESTAS SON LAS QUE USA TU FORMULARIO
    Route::get('/recisa/appointments/list', [AppointmentController::class, 'list'])->name('appointments.list');
    Route::get('/recisa/appointments/add', [AppointmentController::class, 'add'])->name('appointments.create');
    Route::post('/recisa/appointments/add', [AppointmentController::class, 'insert'])->name('appointments.store');
    Route::get('/recisa/appointments/show/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');

    // 🔧 RUTAS AUXILIARES PARA FUNCIONALIDAD OFFLINE/AJAX (limpiando duplicaciones)
    Route::get('/recisa/appointments/get-times/{quota_id}', [AppointmentController::class, 'getTimes'])->name('appointments.get-times');
    Route::get('/recisa/appointments/check-availability/{quota_id}/{date}/{time}', [AppointmentController::class, 'checkAvailability'])->name('appointments.check-availability');

    // 🗑️ RUTA DUPLICADA ELIMINADA (appointments/store ya existe arriba)
    // Route::post('/recisa/appointments/store', [AppointmentController::class, 'insert'])->name('appointments.store-api');

    //Reporte de Pacientes Total
    Route::get('/recisa/patients/reporte', [PatientController::class, 'reporte']);
    Route::get('/recisa/patients/reporte/{dni}', [PatientController::class, 'report_patient']);
    //Reporte de Doctores y su especialidad y pacientes
    Route::get('/recisa/reporte/doctor', [AdminController::class, 'report_doctor']);
});

Route::group(['middleware' => 'profile'], function () {
    //Ruta para ver el perfil
    Route::get('recisa/perfil', [ProfileController::class, 'index']);
    //Enviar los datos del usuario en su perfil
    Route::post('recisa/perfil/edit/{user}', [ProfileController::class, 'update']);
    //Envio de las foto de perfil
    Route::post('recisa/perfil/photo/{user}', [ProfileController::class, 'photo']);
    //Doctor vea sus especialidades y progreso
    Route::get('recisa/specialization/list', [ProfileController::class, 'list']);
});
