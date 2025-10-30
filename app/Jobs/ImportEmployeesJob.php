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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importId;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->importId = Str::uuid();
    }

    public function handle()
    {
        try {
            DB::beginTransaction();
            Excel::import(new EmployeeImport($this->importId), storage_path('app/' . $this->filePath));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error atau notify user
            Log::error('Import failed: ' . $e->getMessage());
        } finally {
            // Hapus file sementara
            Storage::delete($this->filePath);
        }
    }

    public function getImportId()
    {
        return $this->importId;
    }
}