<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;


class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    //Son las columnas que se van a modificar 
    protected $fillable = [
        'dni',
        'names',
        'surnames',
        'phone',
        'age',
        'history_number'
    ];

    protected $casts = [
        'age' => 'date', // <-- Apuntamos a la columna 'age'
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'id_patient'); // Paciente puede tener muchas citas
    }

    public function clinicalHistories()
    {
        return $this->hasMany(ClinicalHistories::class, 'id_patient'); // Paciente puede tener muchas historias clinicas
    }

    //Rutas con slug
    public function getSlugAttribute()
    {
        return Str::slug($this->names);
    }

     protected function calculatedAge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->age) {
                    return $this->age->age;
                }
                return 'N/A';
            }
        );
    }
}
