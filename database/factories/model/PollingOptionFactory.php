<?php

namespace Database\Factories\Model;

use App\Models\PollingOption;
use App\Models\Polling;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollingOptionFactory extends Factory
{
    protected $model = PollingOption::class;

    public function definition(): array
    {
        return [
            'polling_id' => Polling::factory(),
            'option_text' => $this->faker->sentence(3),
        ];
    }
}
