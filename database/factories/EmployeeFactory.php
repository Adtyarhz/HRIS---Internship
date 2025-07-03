<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Kolom unik
            'nik' => $this->faker->unique()->numerify('3201################'),
            'nip' => $this->faker->unique()->numerify('199#######20######'),
            'npwp' => $this->faker->unique()->numerify('##.###.###.#-###.###'),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),

            // Data Pribadi
            'full_name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'religion' => $this->faker->randomElement(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu']),
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-50 years', '-22 years'),
            'marital_status' => $this->faker->randomElement(['Lajang', 'Pernikahan Pertama', 'Cerai Hidup']),
            'dependents' => $this->faker->numberBetween(0, 5),
            'ktp_address' => $this->faker->address(),
            'current_address' => $this->faker->address(),

            // Status Kepegawaian
            'status' => 'Aktif',
            'employee_type' => $this->faker->randomElement(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime']),
            'office' => $this->faker->randomElement(['Kantor Pusat', 'Kantor Cabang']),
            'hire_date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'separation_date' => null,
            'cv_file' => 'cv_' . $this->faker->uuid() . '.pdf',
            'photo' => 'photo_' . $this->faker->uuid() . '.jpg',
            
            // Relasi (Asumsi factory untuk model ini juga ada)
            'division_id' => Division::factory(),
            'position_id' => Position::factory(),
            'user_id' => User::factory(),
        ];
    }
}