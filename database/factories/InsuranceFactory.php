<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Insurance>
 */
class InsuranceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
        // 80% chance to have an expiry date, 5 years after start date
        $expiryDate = $this->faker->optional(0.8)->dateTimeBetween($startDate, '+5 years');

        return [
            // Relasi
            'employee_id' => Employee::factory(),

            // Info Asuransi
            'insurance_number' => $this->faker->unique()->numerify('BPJS-##########'),
            'insurance_type' => $this->faker->randomElement(['KES', 'TK', 'N-BPJS']),
            'start_date' => $startDate,
            'expiry_date' => $expiryDate,
            'status' => 'AKTIF',
            'insurance_file' => 'ins_' . $this->faker->uuid() . '.pdf',
        ];
    }
}