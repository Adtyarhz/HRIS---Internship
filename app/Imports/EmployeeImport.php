<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Position;
use App\Services\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Facades\Broadcast;
use App\Events\ImportProgressUpdated;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, WithEvents
{
    protected $importId;
    protected $totalRows = 0;
    protected $processedRows = 0;

    public function __construct($importId)
    {
        $this->importId = $importId;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $worksheet = $event->reader->getActiveSheet();
                $this->totalRows = $worksheet->getHighestRow() - 1; // Kurangi header
                $this->updateProgress(0);
            },
        ];
    }

    public function model(array $row)
    {
        $user = Auth::user();
        $data = [
            'nik' => $row['nik'],
            'full_name' => $row['full_name'],
            'nip' => $row['nip'] ?? null,
            'npwp' => $row['npwp'] ?? null,
            'gender' => $row['gender'],
            'religion' => $row['religion'],
            'birth_place' => $row['birth_place'],
            'birth_date' => $row['birth_date'] ? Carbon::parse($row['birth_date'])->format('Y-m-d') : null,
            'marital_status' => $row['marital_status'],
            'dependents' => $row['dependents'],
            'ktp_address' => $row['ktp_address'],
            'current_address' => $row['current_address'],
            'phone_number' => $row['phone_number'],
            'email' => $row['email'],
            'status' => $row['status'],
            'employee_type' => $row['employee_type'],
            'office' => $row['office'] ?? null,
            'hire_date' => $row['hire_date'] ? Carbon::parse($row['hire_date'])->format('Y-m-d') : null,
            'separation_date' => $row['separation_date'] ? Carbon::parse($row['separation_date'])->format('Y-m-d') : null,
            'division_id' => $row['division_id'] ?? null,
            'position_id' => $row['position_id'] ?? null,
            'user_id' => $row['user_id'] ?? null,
        ];

        // Auto set division dari position
        if (!empty($data['position_id'])) {
            $position = Position::find($data['position_id']);
            $data['division_id'] = $position?->division_id ?? $data['division_id'];
        }

        if ($user->role === 'hc') {
            // Trigger approval untuk HC
            $tempEmployee = new Employee($data);
            ApprovalWorkflowService::captureModelChange($user, $tempEmployee, 'create');
            $this->incrementProcessed();
            return null;
        }

        // Simpan untuk superadmin
        $employee = Employee::create($data);

        // Buat Career History
        if (!empty($data['position_id'])) {
            \App\Models\CareerHistory::create([
                'employee_id' => $employee->id,
                'position_id' => $data['position_id'],
                'division_id' => $data['division_id'],
                'employee_type' => $data['employee_type'],
                'start_date' => $data['hire_date'],
                'end_date' => null,
                'type' => 'Awal Masuk',
                'notes' => '',
            ]);
        }

        $this->incrementProcessed();
        return $employee;
    }

    public function rules(): array
    {
        return [
            'nik' => 'required|string|size:16|unique:employees,nik|regex:/^[0-9]+$/',
            'full_name' => 'required|string|max:100',
            'nip' => 'nullable|string|max:20|unique:employees,nip|regex:/^[0-9]+$/',
            'npwp' => 'nullable|string|max:20|unique:employees,npwp|regex:/^[0-9]+$/',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => 'required|string|max:20|unique:employees,phone_number|regex:/^\+?[0-9]{8,20}$/',
            'email' => 'required|email|max:100|unique:employees,email',
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
            'employee_type' => ['required', Rule::in(['PKWT', 'PKWTT', 'Probation', 'Intern'])],
            'office' => ['nullable', Rule::in(['Kantor Pusat', 'Kantor Cabang'])],
            'hire_date' => 'required|date',
            'separation_date' => 'nullable|date|after_or_equal:hire_date',
            'division_id' => 'nullable|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => 'nullable|unique:employees,user_id|exists:users,id',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    protected function incrementProcessed()
    {
        $this->processedRows++;
        $progress = ($this->processedRows / $this->totalRows) * 100;
        $this->updateProgress($progress);
    }

    protected function updateProgress($progress)
    {
        broadcast(new ImportProgressUpdated($this->importId, $progress));
    }
}