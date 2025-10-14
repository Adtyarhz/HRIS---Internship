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

   public function index(Request $request)
{
    $query = \App\Models\EmployeeEditRequest::with(['employee', 'approvedBy']);

    // Filter status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Sort (default: terbaru)
    $sort = $request->get('sort', 'desc');
    $query->orderBy('requested_at', $sort);

    // Filter berdasarkan nama karyawan
    if ($request->filled('search')) {
        $search = $request->search;
        $query->whereHas('employee', function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%");
        });
    }

    $requests = $query->paginate(9)->withQueryString();

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
        $changedData = $this->normalizeDates($changedData);

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
            return back()->with('error', 'Your account is not associated with employee data.');
        }

        $originalData = [];
        $changedData = [];

        // Loop setiap tabel & field sesuai mapping
        foreach ($this->tables as $table => $fields) {
            $model = $this->getModelInstance($table, $employee->id, $request->input("{$table}_id"));
            if (!$model) continue;

            foreach ($fields as $field) {
                $oldValue = $model->getRawOriginal($field);
                $newValue = $request->input($field, $oldValue);

                // Normalisasi tanggal
                if ($this->isDateField($field)) {
                    try {
                        if (!empty($oldValue)) {
                            $oldValue = Carbon::parse($oldValue)->format('Y-m-d');
                        }
                        if (!empty($newValue)) {
                            $newValue = Carbon::parse($newValue)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        // biarkan aslinya jika gagal parse
                    }
                }

                // Handle file uploads
                if ($request->hasFile($field)) {
                    $newValue = $request->file($field)->store($table, 'public');
                    Log::debug("New File Uploaded for {$table}.{$field}", [
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'model_id'  => $model->id,
                    ]);
                } else {
                    // normalisasi path lama & baru
                    if (in_array($field, ['certificate_file', 'insurance_file'])) {
                        $oldValue = $this->normalizeFilePath($oldValue, $table, false);
                        $newValue = $this->normalizeFilePath($newValue, $table, false);
                    }
                }

                // Handle material_files (special case)
                if ($field === 'material_files' && in_array($table, ['certifications', 'training_histories'])) {
                    $relationName = ($table === 'certifications') ? 'certificationMaterials' : 'trainingMaterials';
                    $oldFiles = $model->$relationName()->pluck('file_path')->toArray();

                    // normalisasi semua path lama
                    $oldFiles = array_map(fn($f) => $this->normalizeFilePath($f, $table, true), $oldFiles);

                    $newFiles = $oldFiles;
                    // normalisasi semua path lama
                    $oldFiles = array_map(fn($f) => $this->normalizeFilePath($f, $table, true), $oldFiles);

                    if ($request->hasFile('material_files')) {
                        foreach ($request->file('material_files') as $file) {
                            $newFilePath = $file->store("{$table}/materials", 'public');
                            $newFiles[] = $newFilePath;
                            Log::debug("New Material File Uploaded for {$table}", [
                                'path'     => $newFilePath,
                                'model_id' => $model->id,
                            ]);
                        }
                    }

                    if (!empty(array_diff($newFiles, $oldFiles))) {
                        $originalData[$table][$model->id][$field] = $oldFiles;
                        $changedData[$table][$model->id][$field] = $newFiles;
                    }
                    continue;
                }

                // Default comparison
                if ($oldValue !== $newValue) {
                    $originalData[$table][$model->id][$field] = $oldValue;
                    $changedData[$table][$model->id][$field] = $newValue;
                }
            }
        }

        // === Tambahan khusus Career Administration (dari kpi-update) ===
        if ($request->hasAny(['position_id', 'division_id', 'employee_type'])) {
            $originalData['employees'][$employee->id]['position_id'] = $employee->position_id;
            $originalData['employees'][$employee->id]['division_id'] = $employee->division_id;
            $originalData['employees'][$employee->id]['employee_type'] = $employee->employee_type;

            $changedData['employees'][$employee->id]['position_id'] = $request->input('position_id', $employee->position_id);
            $changedData['employees'][$employee->id]['division_id'] = $request->input('division_id', $employee->division_id);
            $changedData['employees'][$employee->id]['employee_type'] = $request->input('employee_type', $employee->employee_type);
        }

        if (empty($changedData)) {
            return back()->with('error', 'No changes proposed.');
        }

        // Normalisasi sebelum simpan (dari master)
        $originalData = $this->normalizeDates($originalData);
        $changedData  = $this->normalizeDates($changedData);

        // === Simpan request dalam transaksi ===
        try {
            DB::beginTransaction();

            $editRequest = EmployeeEditRequest::create([
                'employee_id'  => $employee->id,
                'method'       => 'update',
                'model'        => Employee::class,
                'model_id'     => $employee->id,
                'original_data'=> $originalData,
                'changed_data' => $changedData,
                'status'       => 'waiting',
                'requested_at' => now(),
                'requested_by' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat edit request', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An error occurred while saving the request.');
        }

        // === Kirim notifikasi setelah commit ===
        try {
            $admins = User::whereIn('role', ['hc', 'superadmin'])
                ->whereKeyNot(auth()->id())
                ->get();

            $requesterName = auth()->user()->name ?? ($employee->name ?? 'Karyawan');

            Log::info('Mempersiapkan pengiriman notifikasi EmployeeEditRequest', [
                'edit_request_id' => $editRequest->id,
                'requested_by'    => auth()->id(),
                'recipient_ids'   => $admins->pluck('id')->all(),
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
                    'recipients'      => $admins->pluck('id')->all(),
                ]);
            } else {
                Log::warning('Tidak ada penerima HC/Superadmin untuk edit request', [
                    'edit_request_id' => $editRequest->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi EmployeeEditRequest', [
                'message'         => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
                'edit_request_id' => $editRequest->id ?? null,
            ]);
            return back()->with('success', 'Request berhasil disimpan, namun gagal mengirim notifikasi.');
        }

        return back()->with('success', 'Change request has been successfully submitted and is awaiting approval.');
    }

    public function approve($id)
    {
        $editRequest = EmployeeEditRequest::findOrFail($id);

        $changedData = $editRequest->changed_data ?? [];
        if (!is_array($changedData)) {
            $changedData = json_decode($changedData, true) ?? [];
        }

        $modelClass = $editRequest->model;
        if (!class_exists($modelClass)) {
            throw new \Exception("Model {$editRequest->model} tidak ditemukan.");
        }

        // ====== HANDLE CREATE ======
        if (strtolower($editRequest->method) === 'create') {
            $model = new $modelClass();

            if (!isset($changedData['employee_id'])) {
                $changedData['employee_id'] = $editRequest->employee_id;
            }

            $materialKey = $model instanceof \App\Models\Certification ? 'material_files' : 'material_files_uploaded';
            $materialFiles = $changedData[$materialKey] ?? [];
            unset($changedData[$materialKey]);

            $model->fill($changedData);
            $model->save();

            $this->storeMaterials($model, $materialFiles);

            $editRequest->model_id = $model->id;
            $editRequest->approved_by = auth()->id();
            $editRequest->status = 'approved';
            $editRequest->save();

        // ====== HANDLE DELETE ======
        } elseif (strtolower($editRequest->method) === 'delete') {
            $model = $modelClass::find($editRequest->model_id);

            if (!$model) {
                throw new \Exception("Data {$editRequest->model} dengan ID {$editRequest->model_id} tidak ditemukan.");
            }

            // Simpan backup data sebelum hapus (opsional)
            Log::info("Menghapus data {$modelClass} ID {$editRequest->model_id} atas persetujuan HC/Superadmin.", [
                'deleted_data' => $model->toArray(),
            ]);

            $model->delete();

            $editRequest->approved_by = auth()->id();
            $editRequest->status = 'approved';
            $editRequest->save();

        // ====== HANDLE UPDATE ======
        } else {
            $model = $modelClass::find($editRequest->model_id);
            if (!$model) {
                throw new \Exception("Data {$editRequest->model} dengan ID {$editRequest->model_id} tidak ditemukan.");
            }

            $allowedFields = $this->tables[$editRequest->model] ?? array_keys($changedData);
            $updateData = array_intersect_key($changedData, array_flip($allowedFields));

            $materialKey = $model instanceof \App\Models\Certification ? 'material_files' : 'material_files_uploaded';
            $materialFiles = $changedData[$materialKey] ?? [];
            unset($updateData[$materialKey]);

            // === Special case Employee → handle Career History ===
            if ($model instanceof \App\Models\Employee) {
                $oldPosition = $model->position;
                $oldDivision = $model->division_id;
                $oldType = $model->employee_type;

                $model->update($updateData);
                $model->refresh();

                $newPosition = $model->position;
                $newDivision = $model->division_id;
                $newType = $model->employee_type;

                $careerType = null;
                if (!$oldPosition && $newPosition) {
                    $careerType = 'Awal Masuk';
                } elseif ($oldPosition && $newPosition && $oldPosition->id !== $newPosition->id) {
                    if ($newPosition->depth < $oldPosition->depth) {
                        $careerType = 'Promosi';
                    } elseif ($newPosition->depth > $oldPosition->depth) {
                        $careerType = 'Demosi';
                    } else {
                        $careerType = 'Mutasi';
                    }
                } elseif ($oldDivision != $newDivision || $oldType != $newType) {
                    $careerType = 'Mutasi';
                }

                if ($careerType) {
                    $activeCareer = \App\Models\CareerHistory::where('employee_id', $model->id)
                        ->whereNull('end_date')
                        ->first();

                    if ($activeCareer) {
                        $activeCareer->update(['end_date' => Carbon::today()]);
                    }

                    \App\Models\CareerHistory::create([
                        'employee_id' => $model->id,
                        'position_id' => $newPosition?->id,
                        'division_id' => $newDivision,
                        'employee_type' => $newType,
                        'start_date' => Carbon::today(),
                        'type' => $careerType,
                        'notes' => '',
                    ]);
                }
            } else {
                if (!empty($updateData)) {
                    $model->update($updateData);
                }
            }

            $this->storeMaterials($model, $materialFiles);

            $editRequest->approved_by = auth()->id();
            $editRequest->status = 'approved';
            $editRequest->save();
        }

        // === Kirim notifikasi ke user pengaju ===
        $user = $editRequest->employee->user ?? null;
        if ($user) {
            $user->notify(new EmployeeEditStatusNotification(
                'approved',
                'Your data change request has been approved'
            ));
        }

        return redirect()->back()->with('success', 'Data successfully approved.');
    }

    /**
     * Simpan materials ke tabel relasi
     */
    protected function storeMaterials($model, $materialFiles)
    {
        if (empty($materialFiles))
            return;

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

        return back()->with('error', 'Request successfully rejected.');
    }
    /**
     * Normalisasi path file supaya konsisten (ada folder prefix)
     */
    private function normalizeFilePath($path, $table, $isMaterial = false)
    {
        if (empty($path)) return $path;

        // Jika path sudah ada folder prefix, langsung return
        if (str_contains($path, '/')) {
            return $path;
        }

        return $isMaterial
            ? "{$table}/materials/{$path}"
            : "{$table}/{$path}";
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

    /**
     * Normalisasi path file supaya konsisten (ada folder prefix)
     */
    //private function normalizeFilePath($path, $table, $isMaterial = false)
    //{
      //  if (empty($path)) return $path;

        // Jika path sudah ada folder prefix, langsung return
        //if (str_contains($path, '/')) {
          //  return $path;
        //}

        //return $isMaterial
          //  ? "{$table}/materials/{$path}"
            //: "{$table}/{$path}";
    //}
}
