<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingHistory>
 */
class TrainingHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-3 years', '-1 month');
        // Membuat end_date beberapa hari setelah start_date
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(2, 5) . ' days');

        return [
            // Relasi
            'employee_id' => Employee::factory(),

            // Info Pelatihan
            'training_name' => 'Pelatihan ' . $this->faker->bs(),
            'provider' => $this->faker->company() . ' Training Center',
            'description' => $this->faker->paragraph(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'cost' => $this->faker->randomFloat(2, 1000000, 5000000),
            'location' => $this->faker->city(),
            'certificate_number' => $this->faker->bothify('CERT-####-????-##'),
        ];
    }
}