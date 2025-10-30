<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ValidationGuideSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string
    {
        return 'Petunjuk & Validasi';
    }

    public function headings(): array
    {
        return [
            'Kolom',
            'Wajib?',
            'Tipe Data',
            'Format / Pilihan',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        return [
            ['nik', 'Ya', 'Angka', '16 digit', 'Contoh: 1234567890123456'],
            ['full_name', 'Ya', 'Teks', 'Maks 100 karakter', 'Nama lengkap tanpa gelar'],
            ['nip', 'Tidak', 'Angka', 'Maks 20 digit', 'Opsional'],
            ['npwp', 'Tidak', 'Angka', 'Maks 20 digit', 'Opsional'],
            ['gender', 'Ya', 'Pilihan', "Laki-laki\nPerempuan", 'Pilih salah satu'],
            ['religion', 'Ya', 'Teks', 'Maks 50 karakter', 'Contoh: Islam, Kristen, dll'],
            ['birth_place', 'Ya', 'Teks', 'Maks 50 karakter', 'Kota kelahiran'],
            ['birth_date', 'Ya', 'Tanggal', 'YYYY-MM-DD', 'Contoh: 1990-01-01'],
            ['marital_status', 'Ya', 'Pilihan', "Lajang\nPernikahan Pertama\nPernikahan Kedua\nPernikahan Ketiga\nCerai Hidup\nCerai Mati", 'Pilih salah satu'],
            ['dependents', 'Ya', 'Angka', '≥ 0', 'Jumlah tanggungan'],
            ['ktp_address', 'Ya', 'Teks', 'Bebas', 'Alamat sesuai KTP'],
            ['current_address', 'Ya', 'Teks', 'Bebas', 'Alamat domisili saat ini'],
            ['phone_number', 'Ya', 'Teks', '+62xxxxxxxxxx', 'Unik, maks 20 digit'],
            ['email', 'Ya', 'Email', 'valid@email.com', 'Unik di sistem'],
            ['status', 'Ya', 'Pilihan', "Aktif\nTidak Aktif", 'Status keaktifan'],
            ['employee_type', 'Ya', 'Pilihan', "PKWT\nPKWTT\nProbation\nIntern", 'Jenis kontrak'],
            ['office', 'Tidak', 'Pilihan', "Kantor Pusat\nKantor Cabang", 'Opsional'],
            ['hire_date', 'Ya', 'Tanggal', 'YYYY-MM-DD', 'Tanggal mulai kerja'],
            ['separation_date', 'Tidak', 'Tanggal', 'YYYY-MM-DD', '≥ hire_date, opsional'],
            ['division_id', 'Tidak', 'Angka', 'ID Division', 'Lihat tabel divisions'],
            ['position_id', 'Tidak', 'Angka', 'ID Position', 'Lihat tabel positions'],
            ['user_id', 'Tidak', 'Angka', 'ID User', 'Lihat tabel users, harus belum punya employee'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header bold
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Wrap text untuk kolom "Format / Pilihan"
        $sheet->getStyle('D')->getAlignment()->setWrapText(true);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(40);

        // Warna header
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE4E4E4');

        return [];
    }
}