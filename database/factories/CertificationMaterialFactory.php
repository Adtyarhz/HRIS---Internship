<?php

namespace Database\Factories;

use App\Models\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CertificationMaterial>
 */
class CertificationMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Relasi
            'certification_id' => Certification::factory(),

            // Menyimpan path file palsu
            'file_path' => 'materials/' . $this->faker->uuid() . '.pdf',
        ];
    }
}