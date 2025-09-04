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
use App\Notifications\EmployeeEditRequestNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmployeeEditRequestController extends Controller
{
    private $tables = [
        'employees' => ['name', 'email', 'religion', 'birth_date', 'hire_date', 'separation_date'],
        'certifications' => ['certification_name', 'issuer', 'description', 'date_obtained', 'expiry_date', 'cost', 'certificate_file', 'material_files'],
        'education_histories' => ['education_level', 'institution_name', 'institution_address', 'major', 'start_year', 'end_year', 'gpa_or_score', 'certificate_number'],
        'family_dependents' => ['contact_name', 'relationship', 'phone_number', 'address', 'city', 'province'],
        'health_records' => ['height', 'weight', 'blood_type', 'known_allergies', 'chronic_diseases', 'last_checkup_date', 'checkup_loc', 'price_last_checkup', 'notes'],
        'insurances' => ['insurance_number', 'insurance_type', 'start_date', 'expiry_date', 'status', 'insurance_file'],
        'training_histories' => ['training_name', 'provider', 'description', 'start_date', 'end_date', 'cost', 'location', 'certificate_number', 'material_files'],
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
    $changedData = [];

    foreach ($this->tables as $table => $fields) {
        $model = $this->getModelInstance($table, $employee->id);
        if (!$model) continue;

        foreach ($fields as $field) {
            $newValue = null;
            $oldValue = $model->$field;

            // Handle file uploads secara default (untuk certificate_file dan insurance_file)
            if ($request->hasFile($field)) {
                $newValue = $request->file($field)->store($table, 'public');
                Log::debug("New File Uploaded for {$table}.{$field}", [
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'model_id' => $model->id,
                ]);
            } else {
                $newValue = $oldValue; // Jaga nilai lama jika tidak ada file baru
            }

            // Handle material_files untuk certifications dan training_histories (sesuai training_materials)
            if ($field === 'material_files' && in_array($table, ['certifications', 'training_histories'])) {
                $relationName = ($table === 'certifications') ? 'certificationMaterials' : 'trainingMaterials';
                $oldFiles = $model->$relationName()->pluck('file_path')->toArray();
                $newFiles = $oldFiles;

                if ($request->hasFile('material_files')) {
                    foreach ($request->file('material_files') as $file) {
                        $newFilePath = $file->store("{$table}/materials", 'public');
                        $newFiles[] = $newFilePath;
                        Log::debug("New Material File Uploaded for {$table}", ['path' => $newFilePath, 'model_id' => $model->id]);
                    }
                }

                if (!empty(array_diff($newFiles, $oldFiles)) || $request->hasFile('material_files')) {
                    $originalData[$table][$model->id][$field] = $oldFiles;
                    $changedData[$table][$model->id][$field] = $newFiles;
                }
                continue;
            }

            // Perbandingan untuk field lain (termasuk certificate_file dan insurance_file)
            if ($oldValue !== $newValue || $request->hasFile($field)) {
                $originalData[$table][$model->id][$field] = $oldValue;
                $changedData[$table][$model->id][$field] = $newValue;
            }
        }
    }

    if (empty($changedData)) {
        return back()->with('error', 'Tidak ada perubahan yang diajukan.');
    }

    // simpan request dalam transaksi
    try {
        DB::beginTransaction();

        $editRequest = EmployeeEditRequest::create([
            'employee_id' => $employee->id,
            'method' => 'update',
            'original_data' => $originalData,
            'changed_data' => $changedData,
            'status' => 'waiting',
            'requested_at' => now(),
            'requested_by' => auth()->id(),
        ]);

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Gagal membuat edit request', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return back()->with('error', 'Terjadi kesalahan saat menyimpan request. Silakan coba lagi.');
    }

    // --- Kirim notifikasi SETELAH commit ---
    try {
        // Ambil user dengan role hc/superadmin tanpa bergantung relasi employee
      $admins = User::whereIn('role', ['hc', 'superadmin'])
    ->whereKeyNot(auth()->id())
    ->get();



        $requesterName = auth()->user()->name ?? ($employee->name ?? 'Karyawan');

        Log::info('Mempersiapkan pengiriman notifikasi EmployeeEditRequest', [
            'edit_request_id' => $editRequest->id,
            'requested_by' => auth()->id(),
            'recipient_ids' => $admins->pluck('id')->all(),
        ]);

        if ($admins->isNotEmpty()) {
            foreach ($admins as $admin) {
                $admin->notify(new EmployeeEditRequestNotification(
                    $requesterName,
                    $editRequest->id
                ));
            }

            Log::info('Notifikasi EmployeeEditRequest dikirim', [
                'edit_request_id' => $editRequest->id,
                'recipients' => $admins->pluck('id')->all(),
            ]);
        } else {
            Log::warning('Tidak ada penerima HC/Superadmin untuk edit request', [
                'edit_request_id' => $editRequest->id,
            ]);
        }
    } catch (\Throwable $e) {
        Log::error('Gagal mengirim notifikasi EmployeeEditRequest', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'edit_request_id' => $editRequest->id ?? null,
        ]);
        return back()->with('success', 'Request berhasil disimpan, namun gagal mengirim notifikasi. Admin mungkin perlu dicek secara manual.');
    }

    return back()->with('success', 'Request perubahan berhasil dikirim dan sedang menunggu persetujuan.');
}
  public function approve($id)
{
    $editRequest = EmployeeEditRequest::findOrFail($id);

    // Decode changed data
    $changedData = $editRequest->changed_data ?? [];
    if (!is_array($changedData)) {
        $changedData = json_decode($changedData, true) ?? [];
    }

    $modelClass = $editRequest->model;
    if (!class_exists($modelClass)) {
        throw new \Exception("Model {$editRequest->model} tidak ditemukan.");
    }

    // ==== CASE CREATE ====
    if (strtolower($editRequest->method) === 'create') {
        $model = new $modelClass();

        if (!isset($changedData['employee_id'])) {
            $changedData['employee_id'] = $editRequest->employee_id;
        }

        // Tentukan key material files sesuai model
        $materialKey = $model instanceof \App\Models\Certification ? 'material_files' : 'material_files_uploaded';
        $materialFiles = $changedData[$materialKey] ?? [];
        unset($changedData[$materialKey]);

        // Simpan data utama
        $model->fill($changedData);
        $model->save();

        // Simpan relasi material
        $this->storeMaterials($model, $materialFiles);

        $editRequest->model_id    = $model->id;
        $editRequest->approved_by = auth()->id();
        $editRequest->status      = 'approved';
        $editRequest->save();

    // ==== CASE UPDATE ====
    } else {
        $model = $modelClass::find($editRequest->model_id);
        if (!$model) {
            throw new \Exception("Data {$editRequest->model} dengan ID {$editRequest->model_id} tidak ditemukan.");
        }

        $allowedFields = $this->tables[$editRequest->model] ?? array_keys($changedData);
        $updateData = array_intersect_key($changedData, array_flip($allowedFields));

        // Tentukan key material files sesuai model
        $materialKey = $model instanceof \App\Models\Certification ? 'material_files' : 'material_files_uploaded';
        $materialFiles = $changedData[$materialKey] ?? [];
        unset($updateData[$materialKey]);

        // Update data utama
        if (!empty($updateData)) {
            $model->update($updateData);
        }

        // Update relasi materials
        $this->storeMaterials($model, $materialFiles);

        $editRequest->approved_by = auth()->id();
        $editRequest->status      = 'approved';
        $editRequest->save();
    }

    // 🔔 Notifikasi ke user
    $user = $editRequest->employee->user ?? null;
    if ($user) {
        $user->notify(new EmployeeEditStatusNotification(
            'approved',
            'Your data change request has been approved'
        ));
    }

    return redirect()->back()->with('success', 'Data berhasil di-approve.');
}

/**
 * Simpan materials ke tabel relasi
 */
protected function storeMaterials($model, $materialFiles)
{
    if (empty($materialFiles)) return;

    // Normalisasi biar pasti array of string (path)
    if (is_string($materialFiles)) {
        $materialFiles = json_decode($materialFiles, true) ?? [$materialFiles];
    }

    if (!is_array($materialFiles)) {
        $materialFiles = [$materialFiles];
    }

    // Tentukan relasi sesuai model
    if ($model instanceof \App\Models\TrainingHistory) {
        $relation = 'trainingMaterials';
    } elseif ($model instanceof \App\Models\Certification) {
        $relation = 'certificationMaterials';
    } else {
        return;
    }

    foreach ($materialFiles as $filePath) {
        if (is_array($filePath) && isset($filePath['file_path'])) {
            $filePath = $filePath['file_path'];
        }

        if (!empty($filePath)) {
            $model->$relation()->create([
                'file_path' => $filePath,
            ]);
        }
    }
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

    return back()->with('error', 'Request berhasil ditolak.');
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
