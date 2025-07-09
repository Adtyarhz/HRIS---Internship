<?php

namespace Database\Factories;

use App\Models\RecruitmentProgress;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecruitmentProgressFactory extends Factory
{
    protected $model = RecruitmentProgress::class;

    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'stage' => 'cv_screening',
            'offering_status' => null,
            'status_date' => now(),
            'notes' => $this->faker->sentence,
            'rejected_reason' => null,
            'contract_type' => 'Contract',
            'test_result' => $this->faker->paragraph,
            'result_file' => null,
            'score' => rand(60, 100),
            'slik_recap' => $this->faker->sentence,
        ];
    }
}
