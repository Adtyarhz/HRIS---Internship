<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FamilyDependent>
 */
class FamilyDependentFactory extends Factory
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
            'employee_id' => Employee::factory(),

            // Info Kontak
            'contact_name' => $this->faker->name(),
            'relationship' => $this->faker->randomElement(['Istri', 'Suami', 'Anak', 'Ayah', 'Ibu', 'Mertua']),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'province' => $this->faker->state(), // state() dari faker cocok untuk provinsi
        ];
    }
}