<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeTemplateExport;

class ExportEmployeeTemplateCommand extends Command
{
    protected $signature = 'export:employee-template {output_path : Path untuk simpan file template Excel}';
    protected $description = 'Export template Excel untuk data karyawan';

    public function handle()
    {
        $outputPath = $this->argument('output_path');

        try {
            Excel::store(new EmployeeTemplateExport(), basename($outputPath), 'local');
            // Pindah ke path yang ditentukan
            \Illuminate\Support\Facades\Storage::disk('local')->move(basename($outputPath), 'temp/' . basename($outputPath));
            file_put_contents($outputPath, \Illuminate\Support\Facades\Storage::disk('local')->get('temp/' . basename($outputPath)));
            \Illuminate\Support\Facades\Storage::disk('local')->delete('temp/' . basename($outputPath));

            $this->info('Template Excel berhasil diexport ke: ' . $outputPath);
        } catch (\Exception $e) {
            $this->error('Error export: ' . $e->getMessage());
        }

        return 0;
    }
}