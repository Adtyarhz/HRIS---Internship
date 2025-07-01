<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationHistory>
 */
class EducationHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Membuat logika tahun agar konsisten
        $startYear = $this->faker->numberBetween(2010, 2018);
        $endYear = $startYear + 4; // Asumsi studi 4 tahun untuk S1

        return [
            // Relasi ke Employee
            'employee_id' => Employee::factory(),

            // Data Pendidikan
            'education_level' => $this->faker->randomElement(['SMA', 'D3', 'S1', 'S2']),
            'institution_name' => 'Universitas ' . $this->faker->city,
            'institution_address' => $this->faker->address,
            'major' => $this->faker->randomElement(['Teknik Informatika', 'Sistem Informasi', 'Manajemen', 'Akuntansi', 'Ilmu Komunikasi']),
            'start_year' => (string) $startYear,
            'end_year' => (string) $endYear,
            'gpa_or_score' => $this->faker->randomFloat(2, 3.00, 4.00),
            'certificate_number' => $this->faker->bothify('SKL/##/????-####'),
        ];
    }
}