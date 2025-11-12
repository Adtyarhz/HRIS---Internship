<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importId;

    public function __construct($filePath, $importId)
    {
        $this->filePath = $filePath;
        $this->importId = $importId;
    }

    public function handle()
    {
        $fullPath = storage_path('app/' . $this->filePath);

        Log::info("Starting import job for file: {$this->filePath}, importId: {$this->importId}");

        if (!file_exists($fullPath)) {
            Log::error("File import hilang saat queue: {$this->filePath}");
            return;
        }

        Log::info("File exists at: {$fullPath}");

        try {
            DB::beginTransaction();
            Excel::import(new EmployeeImport($this->importId), $fullPath);
            DB::commit();
            Log::info("Import berhasil: {$this->importId}");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            Log::error('Import validation error: ' . $e->getMessage());
            foreach ($e->failures() as $failure) {
                Log::error("Row {$failure->row()}: " . implode(', ', $failure->errors()));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import queue gagal: ' . $e->getMessage());
        } finally {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
                Log::info("File deleted: {$fullPath}");
            }
        }
    }

    public function getImportId()
    {
        return $this->importId;
    }
}