<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'resume_file' => 'resumes/' . $this->faker->uuid . '.pdf',
            'applied_position' => $this->faker->jobTitle,
            'last_education' => 'Computer Science',
            'origin' => 'Universitas ABC',
            'gpa_score' => '3.50',
            'division_id' => Division::factory(),
        ];
    }
}
