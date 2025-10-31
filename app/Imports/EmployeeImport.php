<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Position;
use App\Services\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading
{
    protected $importId;

    public function __construct($importId)
    {
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        $user = Auth::user();

        // CAST SEMUA FIELD AGAR SESUAI VALIDASI CONTROLLER
        $data = [
            'nik' => (string) ($row['nik'] ?? ''),
            'full_name' => (string) ($row['full_name'] ?? ''),
            'nip' => !empty($row['nip']) ? (string) $row['nip'] : null,
            'npwp' => !empty($row['npwp']) ? (string) $row['npwp'] : null,
            'gender' => (string) ($row['gender'] ?? ''),
            'religion' => (string) ($row['religion'] ?? ''),
            'birth_place' => (string) ($row['birth_place'] ?? ''),
            'birth_date' => $row['birth_date'] ? Carbon::parse($row['birth_date'])->format('Y-m-d') : null,
            'marital_status' => (string) ($row['marital_status'] ?? ''),
            'dependents' => isset($row['dependents']) ? (int) $row['dependents'] : null, // integer
            'ktp_address' => (string) ($row['ktp_address'] ?? ''),
            'current_address' => (string) ($row['current_address'] ?? ''),
            'phone_number' => (string) ($row['phone_number'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'employee_type' => (string) ($row['employee_type'] ?? ''),
            'office' => !empty($row['office']) ? (string) $row['office'] : null,
            'hire_date' => $row['hire_date'] ? Carbon::parse($row['hire_date'])->format('Y-m-d') : null,
            'separation_date' => !empty($row['separation_date']) ? Carbon::parse($row['separation_date'])->format('Y-m-d') : null,
            'division_id' => !empty($row['division_id']) ? (int) $row['division_id'] : null,
            'position_id' => !empty($row['position_id']) ? (int) $row['position_id'] : null,
            'user_id' => !empty($row['user_id']) ? (int) $row['user_id'] : null,
        ];

        // Auto set division dari position
        if (!empty($data['position_id'])) {
            $position = Position::find($data['position_id']);
            if ($position) {
                $data['division_id'] = $position->division_id;
            }
        }

        if ($user && $user->role === 'hc') {
            $tempEmployee = new Employee($data);
            ApprovalWorkflowService::captureModelChange($user, $tempEmployee, 'create');
            return null;
        }

        $employee = Employee::create($data);

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

        return $employee;
    }

    /**
     * VALIDASI 100% SAMA DENGAN CONTROLLER
     */
    public function rules(): array
    {
        return [
            'nik' => 'required|size:16|unique:employees,nik|regex:/^[0-9]+$/',
            'full_name' => 'required|string|max:100',
            'nip' => 'nullable|max:20|unique:employees,nip|regex:/^[0-9]+$/',
            'npwp' => 'nullable|max:20|unique:employees,npwp|regex:/^[0-9]+$/',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => ['required', 'max:20', 'unique:employees,phone_number', 'regex:/^\+?[0-9]{8,20}$/'],
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
}