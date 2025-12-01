<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_quota' => \App\Models\UserSpecialization::inRandomOrder()->first()->id ?? 1,
            'id_patient' => \App\Models\Patient::inRandomOrder()->first()->id ?? 1,
            'date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'time' => $this->faker->time('H:i:s'),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['P', 'C', 'A']), // Pendiente, Cancelado, Atendido
        ];
    }
}
