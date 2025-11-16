<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PatientApiController;
use App\Http\Controllers\Api\AppointmentApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API para la funcionalidad offline de RECISA PWA
| Todas las respuestas son JSON para sincronización con IndexedDB
|
*/

// Rutas públicas (sin autenticación)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/verify', [AuthApiController::class, 'verify']);
    Route::post('/refresh', [AuthApiController::class, 'refresh']);
});

// Rutas protegidas (requieren autenticación)
Route::middleware(['web', 'api.auth'])->group(function () {

    // Datos iniciales para sincronización
    Route::get('/initial-data', [AuthApiController::class, 'initialData']);
    Route::get('/me', [UserApiController::class, 'me']);

    // Pacientes
    Route::prefix('patients')->group(function () {
        Route::get('/', [PatientApiController::class, 'index']);
        Route::get('/search', [PatientApiController::class, 'search']);
        Route::get('/{id}', [PatientApiController::class, 'show']);
        Route::get('/dni/{dni}', [PatientApiController::class, 'findByDni']);
        Route::post('/', [PatientApiController::class, 'store']);
        Route::put('/{id}', [PatientApiController::class, 'update']);
        Route::delete('/{id}', [PatientApiController::class, 'destroy']);
    });

    // Citas/Appointments
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentApiController::class, 'index']);
        Route::get('/today', [AppointmentApiController::class, 'today']);
        Route::get('/stats', [AppointmentApiController::class, 'stats']);
        Route::get('/{id}', [AppointmentApiController::class, 'show']);
        Route::post('/', [AppointmentApiController::class, 'store']);
        Route::put('/{id}', [AppointmentApiController::class, 'update']);
        Route::delete('/{id}', [AppointmentApiController::class, 'destroy']);
    });

    // Usuarios
    Route::prefix('users')->group(function () {
        Route::get('/', [UserApiController::class, 'index']);
        Route::get('/stats', [UserApiController::class, 'stats']);
        Route::get('/doctors', [UserApiController::class, 'doctors']);
        Route::get('/{id}', [UserApiController::class, 'show']);
    });

    // Especializaciones
    Route::get('/specializations', [UserApiController::class, 'specializations']);

    // User Specializations
    Route::get('/user-specializations', [UserApiController::class, 'userSpecializations']);

    // User Groups
    Route::get('/user-groups', [UserApiController::class, 'userGroups']);
});
