<?php

namespace App\Http\Controllers;

use App\Models\EmployeeEditRequest;
use App\Models\Employee;
use App\Models\Certification;
use App\Models\EducationHistory;
use App\Models\FamilyDependent;
use App\Models\HealthRecord;
use App\Models\Insurance;
use App\Models\TrainingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\EmployeeEditStatusNotification;
use Carbon\Carbon;

class EmployeeEditRequestController extends Controller
{
    private $tables = [
        'employees' => ['name', 'email', 'religion', 'birth_date', 'hire_date', 'separation_date'],
        'certifications' => ['certification_name', 'issuer', 'description', 'date_obtained', 'expiry_date', 'cost', 'certificate_file', 'material_files'],
        'education_histories' => ['education_level', 'institution_name', 'institution_address', 'major', 'start_year', 'end_year', 'gpa_or_score', 'certificate_number'],
        'family_dependents' => ['contact_name', 'relationship', 'phone_number', 'address', 'city', 'province'],
        'health_records' => ['height', 'weight', 'blood_type', 'known_allergies', 'chronic_diseases', 'last_checkup_date', 'checkup_loc', 'price_last_checkup', 'notes'],
        'insurances' => ['insurance_number', 'insurance_type', 'start_date', 'expiry_date', 'status', 'insurance_file'],
        'training_histories' => ['training_name', 'provider', 'description', 'start_date', 'end_date', 'cost', 'location', 'certificate_number'],
    ];

    private $modelMap = [
        'employees' => Employee::class,
        'certifications' => Certification::class,
        'education_histories' => EducationHistory::class,
        'family_dependents' => FamilyDependent::class,
        'health_records' => HealthRecord::class,
        'insurances' => Insurance::class,
        'training_histories' => TrainingHistory::class,
    ];

    public function index()
    {
        $requests = EmployeeEditRequest::with(['employee', 'approvedBy'])->latest()->get();
        return view('employee_edit_requests.index', compact('requests'));
    }

  public function show($id)
{
    $editRequest = EmployeeEditRequest::with(['employee', 'approvedBy'])->findOrFail($id);

    // Pastikan original_data & changed_data array
    $originalData = $editRequest->original_data ?? [];
    if (!is_array($originalData)) {
        $originalData = json_decode($originalData, true) ?? [];
    }

    $changedData = $editRequest->changed_data ?? [];
    if (!is_array($changedData)) {
        $changedData = json_decode($changedData, true) ?? [];
    }

    // Normalisasi format date ke Y-m-d
    $originalData = $this->normalizeDates($originalData);
    $changedData  = $this->normalizeDates($changedData);

    return view('employee_edit_requests.show', compact('editRequest', 'originalData', 'changedData'));
}

/**
 * Normalisasi tanggal dalam array agar seragam Y-m-d
 */
private function normalizeDates(array $data)
{
    array_walk_recursive($data, function (&$value, $key) {
        if ($this->isDateField($key) && !empty($value)) {
            try {
                // Saat tampilkan di UI, cukup tanggal saja
                $value = Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                // biarkan nilai aslinya
            }
        }
    });
    return $data;
}


    public function store(Request $request)
{
    $employee = auth()->user()->employee;
    if (!$employee) {
        return back()->with('error', 'Akun Anda tidak terkait dengan data karyawan.');
    }

    $originalData = [];
    $changedData  = [];

    foreach ($this->tables as $table => $fields) {
        $model = $this->getModelInstance($table, $employee->id);
        if (!$model) continue;

        foreach ($fields as $field) {
            $newValue = null;

            // 🔎 Khusus material_files di certifications
            if ($table === 'certifications' && $field === 'material_files') {
                if ($request->hasFile('material_files')) {
                    $paths = [];
                    foreach ($request->file('material_files') as $file) {
                        $paths[] = $file->store('certifications/materials', 'public');
                    }

                    // Data lama dari relasi CertificationMaterial
                    $oldFiles = $model->certificationMaterials()->pluck('file_path')->toArray();

                    if ($paths !== []) {
                        $originalData[$table][$model->id][$field] = $oldFiles;
                        $changedData[$table][$model->id][$field]  = $paths;
                    }
                }
                continue; // skip field berikutnya
            }

            // 🔎 Tangani file upload biasa (certificate_file, insurance_file, dll)
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store($table, 'public');
                $newValue = $path;
            } else {
                $newValue = $request->input("{$table}_{$model->id}_{$field}", $request->input($field));
            }

            if ($newValue === null) continue;

            $oldValue = $model->$field;

            // 🔎 Normalisasi format date
            if ($this->isDateField($field)) {
                $oldValue = $oldValue ? Carbon::parse($oldValue)->format('Y-m-d H:i:s') : null;
                $newValue = $newValue ? Carbon::parse($newValue)->format('Y-m-d H:i:s') : null;
            }

            if ((string) $oldValue !== (string) $newValue) {
                $originalData[$table][$model->id][$field] = $oldValue;
                $changedData[$table][$model->id][$field]  = $newValue;
            }
        }
    }

    if (empty($changedData)) {
        return back()->with('error', 'Tidak ada perubahan yang diajukan.');
    }

    EmployeeEditRequest::create([
        'employee_id'   => $employee->id,
        'method'        => 'update',
        'original_data' => $originalData,
        'changed_data'  => $changedData,
        'status'        => 'waiting',
        'requested_at'  => now(),
        'requested_by'  => auth()->id(),
    ]);

    return back()->with('success', 'Request perubahan berhasil dikirim.');
}


   public function approve($id)
{
    $editRequest = EmployeeEditRequest::findOrFail($id);

    // Pastikan data sudah berbentuk array
    $changedData = $editRequest->changed_data ?? [];
    if (!is_array($changedData)) {
        $changedData = json_decode($changedData, true) ?? [];
    }

    $modelClass = $editRequest->model;

    if (!class_exists($modelClass)) {
        throw new \Exception("Model {$editRequest->model} tidak ditemukan.");
    }

    if (strtolower($editRequest->method) === 'create') {
    $model = new $modelClass();

    if (!isset($changedData['employee_id'])) {
        $changedData['employee_id'] = $editRequest->employee_id;
    }

    // 🔧 Normalisasi certificate_file biar tidak array
    if (isset($changedData['certificate_file']) && is_array($changedData['certificate_file'])) {
        $changedData['certificate_file'] = $changedData['certificate_file'][0] ?? null;
    }
    $changedData['certificate_file'] = $changedData['certificate_file'] ?? null;

    $model->fill($changedData);
    $model->save();

    $editRequest->model_id     = $model->id;
    $editRequest->approved_by  = auth()->id();
    $editRequest->status       = 'approved';
    $editRequest->save();
} else {
        $model = $modelClass::find($editRequest->model_id);
        if (!$model) {
            throw new \Exception("Data {$editRequest->model} dengan ID {$editRequest->model_id} tidak ditemukan.");
        }

        $allowedFields = $this->tables[$editRequest->model] ?? array_keys($changedData);
        $updateData = array_intersect_key($changedData, array_flip($allowedFields));

        if (!empty($updateData)) {
            $model->update($updateData);
        }

        $editRequest->approved_by = auth()->id();
        $editRequest->status      = 'approved';
        $editRequest->save();
    }

    // 🔔 Kirim notifikasi ke user karyawan terkait
    $user = $editRequest->employee->user ?? null;
    if ($user) {
        $user->notify(new EmployeeEditStatusNotification(
            'approved',
            'Your data change request has been approved'
        ));
    }

    return redirect()->back()->with('success', 'Data berhasil di-approve.');
}

public function reject($id)
{
    $editRequest = EmployeeEditRequest::findOrFail($id);

    if ($editRequest->status !== 'waiting') {
        return back()->with('error', 'Request sudah diproses.');
    }

    $editRequest->update([
        'status' => 'rejected',
        'approved_by' => auth()->id(),
    ]);

    // 🔔 Kirim notifikasi ke user karyawan terkait
    $user = $editRequest->employee->user ?? null;
    if ($user) {
        $user->notify(new EmployeeEditStatusNotification(
            'rejected',
            'Your data change request has been rejected'
        ));
    }

    return back()->with('success', 'Request berhasil ditolak.');
}

    private function getModelInstance($table, $employeeId = null, $recordId = null)
    {
        if (!isset($this->modelMap[$table])) {
            return null;
        }

        $modelClass = $this->modelMap[$table];
        if ($recordId) {
            return $modelClass::find($recordId);
        }
        return $modelClass::where('employee_id', $employeeId)->first();
    }

    private function isDateField($field)
    {
        return str_contains($field, 'date') || str_contains($field, 'start_year') || str_contains($field, 'end_year');
    }
}
