<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Division>
 */
class DivisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Menggunakan nama departemen yang umum sebagai contoh
            'name' => $this->faker->unique()->randomElement([
                'Human Resources',
                'Information Technology',
                'Marketing',
                'Finance',
                'Operations',
                'Sales',
                'Legal',
                'Product Management'
            ]),
        ];
    }
}