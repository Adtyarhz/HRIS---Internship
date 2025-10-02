<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User dummy dengan ID = 1 untuk keperluan foreign key created_by
        User::create([
            'id' => 1,
            'name' => 'Dummy Admin',
            'email' => 'dummy@admin.com',
            'password' => bcrypt('password'),
        ]);

        // (Opsional) Data lain untuk testing
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
        UserSeeder::class,
        PositionSeeder::class,
    ]);
        
        // Generate beberapa divisi
        Division::factory()->count(8)->create();

        // Generate beberapa posisi
        // Position::factory()->count(9)->create();
    }
}
