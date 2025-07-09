<?php

namespace Database\Factories;

use App\Models\UserTest;
use App\Models\RecruitmentProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserTestFactory extends Factory
{
    protected $model = UserTest::class;

    public function definition(): array
    {
        return [
            'recruitment_progress_id' => RecruitmentProgress::factory(),
            'test_name' => 'Technical Test ' . $this->faker->word,
            'score' => rand(60, 100),
            'status' => $this->faker->randomElement(['pending', 'done']),
            'notes' => $this->faker->sentence,
            'test_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
