<?php

namespace Database\Factories\Model;

use App\Models\Polling;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollingFactory extends Factory
{
    protected $model = Polling::class;

    public function definition(): array
    {
        return [
            'announcement_id' => Announcement::factory(),
            'deadline' => now()->addDays(3),
            'created_by' => User::factory(),
        ];
    }
}
