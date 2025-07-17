<?php

namespace Database\Factories;

use App\Models\CareerProjection;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CareerProjection>
 */
class CareerProjectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CareerProjection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Relasi ke model lain
            'employee_id' => Employee::factory(),
            'projected_position_id' => Position::factory(),
            'created_by' => User::factory(),

            // Atribut dari model CareerProjection
            'timeline' => $this->faker->randomElement(['1 Tahun', '3 Tahun', '5 Tahun']),
            'status' => $this->faker->randomElement(['Direncanakan', 'Disetujui', 'Tercapai', 'Dibatalkan']),
            'readiness_notes' => $this->faker->optional()->paragraph(3),
        ];
    }
}
