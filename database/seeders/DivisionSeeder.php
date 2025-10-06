<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            'Branch Office',
            'Lending',
            'Funding',
            'Credit Analyst',
            'KMA, SAF, IP',
            'Operation',
            'HC & GA',
            'Brand & Promotion',
            'IT',
        ];

        foreach ($divisions as $division) {
            Division::create([
                'name' => $division,
            ]);
        }
    }
}
