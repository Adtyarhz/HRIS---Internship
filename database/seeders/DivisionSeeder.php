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
            'IT',
            'Internal Audit',
            'Funding',
            'Lending',
            'Branch Office',
            'Operation',
            'HC & GA',
            'KMA, SAF, IP',
            'Credit Analyst',
            'Brand & Promotion',
            'Research & Development',
            'Board of Directors',
            'Board of Commissioners',
        ];

        foreach ($divisions as $division) {
            Division::create([
                'name' => $division,
            ]);
        }
    }
}
