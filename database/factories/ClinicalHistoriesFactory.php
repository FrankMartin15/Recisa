<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicalHistories>
 */
class ClinicalHistoriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_patient' => \App\Models\Patient::inRandomOrder()->first()->id ?? 1,
            'datetime_created' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'source_pdf' => 'clinical_histories/dummy.pdf', // Placeholder
        ];
    }
}
