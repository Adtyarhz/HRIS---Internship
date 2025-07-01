<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certification>
 */
class CertificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateObtained = $this->faker->dateTimeBetween('-5 years', '-1 year');
        $expiryDate = $this->faker->optional(0.7)->dateTimeBetween($dateObtained, '+3 years'); // 70% chance to have an expiry date

        return [
            // Relasi
            'employee_id' => Employee::factory(),

            // Info Sertifikasi
            'certification_name' => $this->faker->randomElement([
                'Certified Laravel Developer',
                'AWS Certified Solutions Architect - Associate',
                'Google Certified Professional Cloud Architect',
                'Certified Information Systems Security Professional (CISSP)',
                'Project Management Professional (PMP)'
            ]),
            'issuer' => $this->faker->randomElement(['Laracasts', 'Amazon Web Services', 'Google Cloud', '(ISC)²', 'PMI']),
            'description' => $this->faker->paragraph(2),
            'date_obtained' => $dateObtained,
            'expiry_date' => $expiryDate,
            'cost' => $this->faker->randomFloat(2, 500000, 3000000),
            'certificate_file' => 'cert_' . $this->faker->uuid() . '.pdf',
        ];
    }
}