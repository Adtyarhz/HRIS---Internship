<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;
use App\Jobs\ImportEmployeesJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportEmployeesCommand extends Command
{
    protected $signature = 'import:employees {file_path : Path ke file Excel untuk import (xlsx, xls, csv)} {--sync : Jalankan import secara synchronous}';
    protected $description = 'Import data karyawan dari file Excel untuk migrasi awal';

    public function handle()
    {
        $originalPath = $this->argument('file_path');

        // 1. Validasi file ada
        if (!file_exists($originalPath) || !is_readable($originalPath)) {
            $this->error('File tidak ditemukan atau tidak bisa dibaca: ' . $originalPath);
            return 1;
        }

        // 2. Buat nama unik
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $fileName = 'imports/' . Str::uuid() . '.' . $extension;
        $fullStoragePath = storage_path('app/' . $fileName);

        // 3. Pastikan folder imports ada
        $directory = dirname($fullStoragePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // 4. Copy file secara langsung (lebih aman dari file_get_contents)
        if (!copy($originalPath, $fullStoragePath)) {
            $this->error('Gagal menyalin file ke storage: ' . $originalPath);
            return 1;
        }

        // 5. Validasi file benar-benar tersalin
        if (!file_exists($fullStoragePath)) {
            $this->error('File gagal disimpan di storage: ' . $fullStoragePath);
            return 1;
        }

        // 6. Cek jumlah baris
        try {
            $reader = Excel::toArray([], $fullStoragePath);
            $rowCount = count($reader[0] ?? []) - 1;
            if ($rowCount < 0) $rowCount = 0;
        } catch (\Exception $e) {
            @unlink($fullStoragePath); // hapus jika gagal
            $this->error('File Excel rusak atau tidak valid: ' . $e->getMessage());
            return 1;
        }

        $importId = (string) Str::uuid();

        if ($this->option('sync') || $rowCount <= 100) {
            try {
                DB::beginTransaction();
                Excel::import(new EmployeeImport($importId), $fullStoragePath);
                DB::commit();
                $this->info("Berhasil import {$rowCount} baris (sync).");
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                DB::rollBack();
                foreach ($e->failures() as $failure) {
                    $this->error("Baris {$failure->row()}: " . implode(', ', $failure->errors()));
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Gagal import: ' . $e->getMessage());
            }
        } else {
            ImportEmployeesJob::dispatch($fileName, $importId)->onQueue('default');
            $this->info("Impor besar ({$rowCount} baris) dikirim ke queue. ID: {$importId}");
        }

        // 7. Selalu hapus file
        @unlink($fullStoragePath);

        return 0;
    }
}