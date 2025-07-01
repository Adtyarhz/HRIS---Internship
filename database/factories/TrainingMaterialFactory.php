<?php

namespace Database\Factories;

use App\Models\TrainingHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingMaterial>
 */
class TrainingMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Relasi ke TrainingHistory
            'training_id' => TrainingHistory::factory(),

            // Menyimpan path file palsu
            'file_path' => 'training_docs/' . $this->faker->uuid() . '.zip',
        ];
    }
}