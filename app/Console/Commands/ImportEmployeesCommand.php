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
    protected $signature = 'import:employees {file_path : Path ke file Excel untuk import (xlsx, xls, csv)}';
    protected $description = 'Import data karyawan dari file Excel untuk migrasi awal';

    public function handle()
    {
        $filePath = $this->argument('file_path');

        if (!file_exists($filePath)) {
            $this->error('File tidak ditemukan: ' . $filePath);
            return 1;
        }

        // Copy file ke storage Laravel untuk proses
        $storedPath = 'imports/' . basename($filePath);
        Storage::put($storedPath, file_get_contents($filePath));

        // Cek jumlah rows untuk decide sync atau queue
        $reader = Excel::toArray(new EmployeeImport(Str::uuid()), storage_path('app/' . $storedPath));
        $rowCount = count($reader[0]) - 1; // Kurangi header

        if ($rowCount > 100) {
            // Queue jika besar
            $job = new ImportEmployeesJob($storedPath);
            $job->dispatch();
            $this->info('Impor data besar sedang diproses di queue. ID: ' . $job->getImportId());
        } else {
            // Sync jika kecil
            try {
                DB::beginTransaction();
                Excel::import(new EmployeeImport(Str::uuid()), storage_path('app/' . $storedPath));
                DB::commit();
                $this->info('Data karyawan berhasil diimpor secara sync.');
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                DB::rollBack();
                $failures = $e->failures();
                $this->error('Error validasi: ' . json_encode($failures));
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Error impor: ' . $e->getMessage());
            }
        }

        // Hapus file sementara
        Storage::delete($storedPath);

        return 0;
    }
}