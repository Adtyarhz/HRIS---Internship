<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Menggunakan jabatan pekerjaan yang umum sebagai contoh
            'title' => $this->faker->unique()->randomElement([
                'Staff IT Support',
                'Frontend Developer',
                'Backend Developer',
                'Fullstack Developer',
                'UI/UX Designer',
                'Digital Marketing Specialist',
                'Accountant',
                'HR Generalist',
                'Office Manager'
            ]),
        ];
    }
}