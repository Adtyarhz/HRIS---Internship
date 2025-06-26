<?php

namespace Database\Factories;

use App\Models\PollingVote;
use App\Models\PollingOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollingVoteFactory extends Factory
{
    protected $model = PollingVote::class;

    public function definition(): array
    {
        return [
            'polling_option_id' => PollingOption::factory(),
            'created_by' => User::factory(),
        ];
    }
}
