<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Employee;
use App\Models\Division;
use App\Models\Position;

class UserSeeder extends Seeder
{
    // Daftar nama dan kota untuk data yang lebih realistis
    private $maleFirstNames = ['Budi', 'Joko', 'Agus', 'Eko', 'Asep', 'Doni', 'Rian', 'Fajar', 'Aditya', 'Rizky'];
    private $femaleFirstNames = ['Siti', 'Dewi', 'Sri', 'Ani', 'Putri', 'Rina', 'Wulan', 'Dian', 'Fitri', 'Nina'];
    private $lastNames = ['Santoso', 'Wijaya', 'Kusuma', 'Gunawan', 'Pratama', 'Wibowo', 'Nugroho', 'Setiawan', 'Susanto', 'Halim'];
    private $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Palembang', 'Depok', 'Tangerang', 'Yogyakarta'];

    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $positions = Position::pluck('id', 'title')->all();
        $divisions = Division::all();

        // ===== PENGGUNA TINGKAT ATAS (SUPERADMIN & DIREKSI) =====
        $this->createSuperAdmin($faker, $positions);
        $this->createPresidentDirector($faker, $positions);

        // ===== PENGGUNA PER DIVISI (1 Manager, 1 Section Head, 2 Staff) =====
        foreach ($divisions as $division) {
            $this->createDivisionTeam($faker, $division, $positions);
        }

        $this->command->info('✅ UserSeeder selesai. Semua role dan divisi berhasil dibuat dengan data acak.');
    }

    /**
     * Membuat pengguna Superadmin.
     */
    private function createSuperAdmin($faker, array $positions)
    {
        $user = User::create([
            'name' => 'adminbprperdana',
            'email' => 'admin@bprperdana.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        $this->createEmployee($faker, [
            'full_name' => 'Admin HRIS',
            'email' => 'admin@bpr.com',
            'gender' => 'Laki-laki',
            'position_id' => $positions['IT DevSecOps'] ?? null, // Superadmin diasumsikan dari IT
            'division_id' => Division::where('name', 'IT')->first()->id,
            'user_id' => $user->id,
            'hire_date' => now()->subYears(5),
        ]);
    }

    /**
     * Membuat pengguna Direktur Utama (President Director).
     */
    private function createPresidentDirector($faker, array $positions)
    {
        $name = $this->getRandomName('Laki-laki');
        $user = User::create([
            'name' => $name,
            'email' => 'direktur.utama@bprperdana.com',
            'password' => Hash::make('password'),
            'role' => 'direksi',
        ]);

        $this->createEmployee($faker, [
            'full_name' => $name,
            'email' => 'direktur.utama@bprperdana.com',
            'gender' => 'Laki-laki',
            'position_id' => $positions['President Director'] ?? null,
            'division_id' => null, // Direktur Utama tidak memiliki divisi spesifik
            'user_id' => $user->id,
            'hire_date' => now()->subYears(10),
        ]);
    }

    /**
     * Membuat satu tim (Manajer, Kepala Seksi, Staf) untuk sebuah divisi.
     */
    private function createDivisionTeam($faker, Division $division, array $positions)
    {
        // Tentukan posisi berdasarkan nama divisi
        list($managerPos, $sectionHeadPos, $staffPositions) = $this->getPositionsForDivision($division->name);
        
        // Buat Manajer
        if ($managerPos && isset($positions[$managerPos])) {
            $managerGender = $faker->randomElement(['Laki-laki', 'Perempuan']);
            $managerName = $this->getRandomName($managerGender);
            $managerUser = User::create([
                'name' => $managerName,
                'email' => strtolower(str_replace(' ', '.', $managerName)) . '@bprperdana.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]);
            $this->createEmployee($faker, [
                'full_name' => $managerName,
                'email' => $managerUser->email,
                'gender' => $managerGender, // FIX: Menggunakan variabel gender yang sudah dibuat
                'position_id' => $positions[$managerPos],
                'division_id' => $division->id,
                'user_id' => $managerUser->id,
                'hire_date' => now()->subYears(6),
            ]);
        }

        // Buat Section Head
        if ($sectionHeadPos && isset($positions[$sectionHeadPos])) {
            $sectionHeadGender = $faker->randomElement(['Laki-laki', 'Perempuan']);
            $sectionHeadName = $this->getRandomName($sectionHeadGender);
            $sectionHeadUser = User::create([
                'name' => $sectionHeadName,
                'email' => strtolower(str_replace(' ', '.', $sectionHeadName)) . '@bprperdana.com',
                'password' => Hash::make('password'),
                'role' => 'section_head',
            ]);
            $this->createEmployee($faker, [
                'full_name' => $sectionHeadName,
                'email' => $sectionHeadUser->email,
                'gender' => $sectionHeadGender, // FIX: Menggunakan variabel gender yang sudah dibuat
                'position_id' => $positions[$sectionHeadPos],
                'division_id' => $division->id,
                'user_id' => $sectionHeadUser->id,
                'hire_date' => now()->subYears(3),
            ]);
        }

        // Buat 2 Staff
        for ($i = 0; $i < 2; $i++) {
            if (!empty($staffPositions)) {
                // Pilih posisi staf secara acak dari yang tersedia untuk divisi tersebut
                $staffPosTitle = $faker->randomElement($staffPositions);
                if (isset($positions[$staffPosTitle])) {
                    $staffGender = $faker->randomElement(['Laki-laki', 'Perempuan']);
                    $staffName = $this->getRandomName($staffGender);
                    $role = ($division->name === 'Lending' || $division->name === 'Funding') ? 'staff_bisnis' : 'staff_support';

                    $staffUser = User::create([
                        'name' => $staffName,
                        'email' => strtolower(str_replace(' ', '.', $staffName)) . $i . '@bprperdana.com',
                        'password' => Hash::make('password'),
                        'role' => $role,
                    ]);

                    $this->createEmployee($faker, [
                        'full_name' => $staffName,
                        'email' => $staffUser->email,
                        'gender' => $staffGender, // FIX: Menggunakan variabel gender yang sudah dibuat
                        'position_id' => $positions[$staffPosTitle],
                        'division_id' => $division->id,
                        'user_id' => $staffUser->id,
                        'hire_date' => now()->subYears(1),
                    ]);
                }
            }
        }
    }

    /**
     * Helper untuk membuat data Employee.
     */
    private function createEmployee($faker, array $data)
    {
        $defaults = [
            'nik' => $faker->unique()->numerify('3276##########'),
            'religion' => 'Islam',
            'birth_place' => $faker->randomElement($this->cities),
            'birth_date' => $faker->dateTimeBetween('-40 years', '-22 years')->format('Y-m-d'),
            'marital_status' => 'Lajang',
            'dependents' => 0,
            'ktp_address' => $faker->address,
            'current_address' => $faker->address,
            'phone_number' => $faker->unique()->numerify('08##########'),
            'status' => 'Aktif',
            'employee_type' => 'Fulltime',
            'office' => 'Kantor Pusat',
        ];

        Employee::create(array_merge($defaults, $data));
    }

    /**
     * Menghasilkan nama acak Indonesia.
     */
    private function getRandomName(string $gender): string
    {
        $firstName = ($gender === 'Laki-laki')
            ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
            : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        return "{$firstName} {$lastName}";
    }

    /**
     * Memetakan nama divisi ke judul posisi yang sesuai dari PositionSeeder.
     */
    private function getPositionsForDivision(string $divisionName): array
    {
        switch ($divisionName) {
            case 'Branch Office':
                return ['Branch Manager', 'Operation Section Head (Branch)', ['Collection Officer (Branch)', 'Funding Officer (Branch)']];
            case 'Lending':
                return ['Loan Manager 1', 'Individual Loan Sc. Head (Loan 1)', ['Individual Loan Officer (Loan 1)', 'Collection Officer (Loan 1)']];
            case 'Funding':
                return ['Funding Manager', 'Funding Sc. Head', ['Funding Officer', 'Funding Officer (Commercial)']];
            case 'Credit Analyst':
                return [null, 'Credit Analyst Sc. Head', ['Credit Analyst Officer']]; // Tidak ada manajer, langsung di bawah direktur
            case 'KMA, SAF, IP':
                return [null, 'KMA, SAF, IP Sc. Head', ['KMA, SAF, IP Officer']]; // Tidak ada manajer
            case 'Operation':
                return ['Operation Manager', 'Operation Section Head (HO)', ['Accounting Officer', 'Loan Admin Officer (HO)']];
            case 'HC & GA':
                return ['HC & GA Manager', 'HC & GA Sc. Head', ['HC & GA Officer']];
            case 'Brand & Promotion':
                return ['Branding & Promotion Manager', 'Branding & Promotion Sc. Head', ['Branding & Promotion Officer']];
            case 'IT':
                return ['Business Development & IT Manager', 'IT Sc. Head', ['IT Development & Database Development', 'IT DevSecOps']];
            default:
                return [null, null, []];
        }
    }
}

