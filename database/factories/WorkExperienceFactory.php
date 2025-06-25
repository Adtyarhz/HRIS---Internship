<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkExperience>
 */
class WorkExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Membuat logika tanggal yang konsisten
        $startDate = $this->faker->dateTimeBetween('-10 years', '-3 years');
        $endDate = $this->faker->dateTimeBetween($startDate, '-1 year');

        return [
            // Relasi
            'employee_id' => Employee::factory(),

            // Info Perusahaan
            'company_name' => $this->faker->company(),
            'company_address' => $this->faker->address(),
            'company_phone' => $this->faker->phoneNumber(),
            
            // Info Pekerjaan
            'position_title' => $this->faker->jobTitle(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'responsibilities' => $this->faker->paragraph(3),
            'reason_to_leave' => $this->faker->sentence(),
            'last_salary' => $this->faker->randomFloat(2, 5000000, 15000000),

            // File (cukup simpan nama file palsu)
            'reference_letter_file' => 'ref_' . $this->faker->uuid() . '.pdf',
            'salary_slip_file' => 'slip_' . $this->faker->uuid() . '.pdf',
        ];
    }
}