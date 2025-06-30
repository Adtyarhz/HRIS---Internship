<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthRecord>
 */
class HealthRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Relasi ke Employee
            'employee_id' => Employee::factory(),

            // Data Fisik
            'height' => $this->faker->randomFloat(1, 150.0, 190.0), // Tinggi badan dalam cm
            'weight' => $this->faker->randomFloat(1, 50.0, 100.0), // Berat badan dalam kg
            'blood_type' => $this->faker->randomElement(['A', 'B', 'AB', 'O', '-']),

            // Riwayat Penyakit
            'known_allergies' => $this->faker->randomElement(['Tidak ada', 'Debu', 'Udang', 'Obat-obatan tertentu', null]),
            'chronic_diseases' => $this->faker->randomElement(['Tidak ada', 'Asma', 'Hipertensi', null]),

            // Data Checkup
            'last_checkup_date' => $this->faker->dateTimeThisYear(),
            'checkup_loc' => 'Klinik ' . $this->faker->company(),
            'price_last_checkup' => (string) $this->faker->numberBetween(150000, 500000),
            'notes' => $this->faker->sentence(),
        ];
    }
}