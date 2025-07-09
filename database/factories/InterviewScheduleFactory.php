<?php

namespace Database\Factories;

use App\Models\InterviewSchedule;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterviewScheduleFactory extends Factory
{
    protected $model = InterviewSchedule::class;

    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'interview_type' => $this->faker->randomElement(['User', 'HC', 'Direksi']),
            'interview_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'interviewer' => $this->faker->name,
            'location' => $this->faker->address,
            'result' => $this->faker->optional()->sentence,
        ];
    }
}
