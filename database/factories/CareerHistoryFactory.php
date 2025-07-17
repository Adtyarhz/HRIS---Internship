<?php

namespace Database\Factories;

use App\Models\CareerHistory;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CareerHistory>
 */
class CareerHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CareerHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ambil tanggal mulai secara acak dalam 5 tahun terakhir
        $startDate = $this->faker->dateTimeBetween('-5 years', 'now');

        return [
            // Relasi ke model lain
            'employee_id' => Employee::factory(),
            'position_id' => Position::factory(),
            'division_id' => Division::factory(),

            // Atribut dari model CareerHistory
            'employee_type' => $this->faker->randomElement(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime']),
            'start_date' => $startDate,
            // End date bisa null atau setelah start date
            'end_date' => $this->faker->optional(0.3)->dateTimeBetween($startDate, '+2 years'),
            'type' => $this->faker->randomElement(['Promosi', 'Mutasi', 'Demosi', 'Awal Masuk']),
            'notes' => $this->faker->optional()->sentence(10),
        ];
    }
}
