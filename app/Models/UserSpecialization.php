<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // O use Illuminate\Database\Eloquent\Relations\Pivot; si es una tabla pivote

class UserSpecialization extends Model // O Pivot
{
    use HasFactory;

    protected $table = 'user_specialization';

    protected $fillable = [
        'id_user',          // Asegúrate de que estas columnas existan y puedan ser asignadas masivamente si es necesario
        'id_specialization',
        'cupo_doctor'
    ];

    // Define la relación para el usuario
    public function user()
    {
        // El primer parámetro es el modelo relacionado.
        // El segundo (opcional) es la clave foránea en ESTA tabla (user_specialization).
        // El tercero (opcional) es la clave primaria en la tabla relacionada (users).
        return $this->belongsTo(User::class, 'id_user'); // Asume que la FK es 'id_user' y PK en User es 'id'
    }

    // Define la relación para la especialización
    public function specialization()
    {
        // Asume que la FK es 'id_specialization' y PK en Specialization es 'id'
        return $this->belongsTo(Specialization::class, 'id_specialization');
    }

    // Define la relación inversa: una UserSpecialization puede tener muchas citas
    public function appointment(){ // El nombre debe ser `appointments` para seguir convención si es hasMany
                                 // pero si Appointment tiene belongsTo UserSpecialization, este nombre es 'ok'
                                 // aunque `appointments` sería más estándar para una relación `hasMany`.
        return $this->hasMany(Appointment::class,'id_quota'); // 'id_quota' es la FK en la tabla 'appointments'
    }
}