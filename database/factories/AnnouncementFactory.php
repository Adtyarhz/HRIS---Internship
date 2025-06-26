<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => $this->faker->sentence(),
            'announcement_type' => $this->faker->randomElement(['Umum', 'Divisi', 'Urgent', 'Informasi', 'Polling']),
            'label' => $this->faker->word(),
            'content' => $this->faker->paragraph(),
            'attachment_file' => null,
            'external_link' => $this->faker->url(),
        ];
    }
}
