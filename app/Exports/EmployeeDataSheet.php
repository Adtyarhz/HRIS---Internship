<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class EmployeeDataSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Data Karyawan';
    }

    public function headings(): array
    {
        return [
            'nik',
            'full_name',
            'nip',
            'npwp',
            'gender',
            'religion',
            'birth_place',
            'birth_date',
            'marital_status',
            'dependents',
            'ktp_address',
            'current_address',
            'phone_number',
            'email',
            'status',
            'employee_type',
            'office',
            'hire_date',
            'separation_date',
            'division_id',
            'position_id',
            'user_id',
        ];
    }

    public function array(): array
    {
        return [
            // Contoh baris (akan muncul di Excel sebagai panduan)
            [
                '1234567890123456',
                'John Doe',
                '12345678',
                '12345678901234567890',
                'Laki-laki', // atau 'Perempuan'
                'Islam',
                'Jakarta',
                '1990-01-01',
                'Lajang',
                0,
                'Jl. KTP No. 123',
                'Jl. Domisili No. 456',
                '+6281234567890',
                'john.doe@company.com',
                'Aktif',
                'PKWTT',
                'Kantor Pusat',
                '2023-01-01',
                null, // atau '2025-12-31'
                1, // ID Division
                5, // ID Position
                10, // ID User (opsional)
            ],
        ];
    }
}