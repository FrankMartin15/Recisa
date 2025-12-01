<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dni' => $this->faker->unique()->numerify('########'),
            'names' => substr($this->faker->firstName(), 0, 20),
            'surnames' => substr($this->faker->lastName() . ' ' . $this->faker->lastName(), 0, 25),
            'phone' => $this->faker->unique()->numerify('9########'),
            'age' => $this->faker->date('Y-m-d', '2005-01-01'),
            'history_number' => $this->faker->unique()->numerify('##########'),
        ];
    }
}
