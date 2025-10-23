<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Certification;
use App\Models\CertificationMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;
use App\Services\ApprovalWorkflowService;

class CertificationController extends Controller
{
    /**
     * Ensure the user has access to this employee's data.
     */
    private function authorizeAccess(Employee $employee)
    {
        $user = Auth::user();

        // If not HC or Superadmin, only allow access to own data
        if (!in_array($user->role, ['hc', 'superadmin']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have permission to access this data.');
        }
    }

    public function index(Employee $employee)
    {
        $this->authorizeAccess($employee);

        $certifications = $employee->certifications()
            ->with('certificationMaterials')
            ->latest('date_obtained')
            ->get();

        return view('employees.certifications.index', compact('employee', 'certifications'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeAccess($employee);

        return view('employees.certifications.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $validatedData = $request->validate([
            'certification_name' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_obtained' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:date_obtained',
            'cost' => 'nullable|numeric|min:0',
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'material_files' => 'nullable|array|max:10',
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240',
        ]);

        $user = Auth::user();
        DB::beginTransaction();
        try {
            if (!in_array($user->role, ['hc', 'superadmin'])) {
                // Store the main file
                if ($request->hasFile('certificate_file')) {
                    $mainFile = $request->file('certificate_file');
                    $mainFileName = time() . '_cert_' . Str::slug(pathinfo($mainFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $mainFile->getClientOriginalExtension();
                    $mainFile->storeAs('certifications', $mainFileName, 'public');

                    // Store the file path as a string
                    $validatedData['certificate_file'] = 'certifications/' . $mainFileName;
                }

                // Store material files if provided
                $materials = [];
                if ($request->hasFile('material_files')) {
                    foreach ($request->file('material_files') as $materialFile) {
                        $materialName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                            . '.' . $materialFile->getClientOriginalExtension();
                        $materialFile->storeAs('certifications/materials', $materialName, 'public');
                        $materials[] = 'certifications/materials/' . $materialName;
                    }
                }

                // Store material file paths as a string array/json
                $validatedData['material_files'] = $materials;

                // Send to service (data is cleaned of file objects)
                $notifier = new RequestNotifierService();
                $editRequest = $notifier->createEditRequest(
                    new Certification(),
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create certification request.');
                }

                // Buat model sementara untuk approval
                $tempModel = new Certification([
                    'employee_id' => $employee->id,
                    'certification_name' => $validatedData['certification_name'],
                    'issuer' => $validatedData['issuer'],
                    'description' => $validatedData['description'] ?? null,
                    'date_obtained' => $validatedData['date_obtained'],
                    'expiry_date' => $validatedData['expiry_date'] ?? null,
                    'cost' => $validatedData['cost'] ?? null,
                    'certificate_file' => $validatedData['certificate_file'],
                ]);

                // Gunakan ApprovalWorkflowService langsung
                $cdr = ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create', [
                    'related_files' => [
                        'new_materials' => $materials,
                        'delete_materials' => [],
                    ],
                ]);

                if (!$cdr) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create certification request.');
                }

                DB::commit();
                return redirect()->route('employees.certifications.index', $employee->id)
                    ->with('info', 'Certification addition request has been sent and is awaiting approval.');
            }

            /**
             * 2️⃣ HC → KIRIM REQUEST APPROVAL (pakai folder TEMP)
             */
            if ($user->role === 'hc') {
                // Simpan file ke folder sementara (temp)
                $mainFile = $request->file('certificate_file');
                $mainFileName = time() . '_cert_' . Str::slug(pathinfo($mainFile->getClientOriginalName(), PATHINFO_FILENAME))
                    . '.' . $mainFile->getClientOriginalExtension();
                $mainFile->storeAs('certifications/temp', $mainFileName, 'public');

                $materials = [];
                if ($request->hasFile('material_files')) {
                    foreach ($request->file('material_files') as $materialFile) {
                        $materialName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                            . '.' . $materialFile->getClientOriginalExtension();
                        $materialFile->storeAs('certifications/materials/temp', $materialName, 'public');
                        $materials[] = 'certifications/materials/temp/' . $materialName;
                    }
                }

                // Buat model sementara untuk approval
                $tempModel = new Certification([
                    'employee_id' => $employee->id,
                    'certification_name' => $validatedData['certification_name'],
                    'issuer' => $validatedData['issuer'],
                    'description' => $validatedData['description'] ?? null,
                    'date_obtained' => $validatedData['date_obtained'],
                    'expiry_date' => $validatedData['expiry_date'] ?? null,
                    'cost' => $validatedData['cost'] ?? null,
                    'certificate_file' => 'certifications/temp/' . $mainFileName,
                ]);

                // Capture untuk approval
                ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create', [
                    'related_files' => [
                        'new_materials' => $materials,
                        'delete_materials' => [],
                    ],
                ]);

                DB::commit();
                return redirect()->route('employees.certifications.index', $employee->id)
                    ->with('success', 'Certification creation request has been sent for approval.');
            }

            /**
             * 3️⃣ SUPERADMIN → langsung buat data permanen
             */
            $mainFile = $request->file('certificate_file');
            $mainFileName = time() . '_cert_' . Str::slug(pathinfo($mainFile->getClientOriginalName(), PATHINFO_FILENAME))
                . '.' . $mainFile->getClientOriginalExtension();
            $mainFile->storeAs('certifications', $mainFileName, 'public');

            $certification = Certification::create([
                'employee_id' => $employee->id,
                'certification_name' => $validatedData['certification_name'],
                'issuer' => $validatedData['issuer'],
                'description' => $validatedData['description'] ?? null,
                'date_obtained' => $validatedData['date_obtained'],
                'expiry_date' => $validatedData['expiry_date'] ?? null,
                'cost' => $validatedData['cost'] ?? null,
                'certificate_file' => 'certifications/' . $mainFileName,
            ]);

            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $materialName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    $materialFile->storeAs('certifications/materials', $materialName, 'public');
                    $certification->certificationMaterials()->create([
                        'file_path' => $materialName
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('employees.certifications.index', $employee->id)
                ->with('success', 'Certification and materials added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('public')->delete('certifications/main/' . ($mainFileName ?? ''));
            return back()->with('error', 'Failed to save certification: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, Certification $certification)
    {
        $this->authorizeAccess($employee);

        if ($certification->employee_id !== $employee->id) {
            abort(403, 'Certification is not associated with this employee.');
        }

        $certification->load('certificationMaterials');
        return view('employees.certifications.edit', compact('employee', 'certification'));
    }

    public function update(Request $request, Employee $employee, Certification $certification)
    {
        $this->authorizeAccess($employee);

        if ($certification->employee_id !== $employee->id) {
            abort(403, 'Certification is not associated with this employee.');
        }

        $validatedData = $request->validate([
            'certification_name' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_obtained' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:date_obtained',
            'cost' => 'nullable|numeric|min:0',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'material_files' => 'nullable|array|max:10',
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240',
            'delete_materials' => 'nullable|array',
            'delete_materials.*' => 'exists:certification_materials,id'
        ]);

        $user = Auth::user();
        DB::beginTransaction();

        try {
            if (!in_array($user->role, ['hc', 'superadmin'])) {
                // Store the main file
                if ($request->hasFile('certificate_file')) {
                    $mainFile = $request->file('certificate_file');
                    $mainFileName = time() . '_cert_' . Str::slug(pathinfo($mainFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $mainFile->getClientOriginalExtension();
                    $mainFile->storeAs('certifications', $mainFileName, 'public');

                    // Store the file path as a string
                    $validatedData['certificate_file'] = 'certifications/' . $mainFileName;
                }

                // Store material files if provided
                $materials = [];
                if ($request->hasFile('material_files')) {
                    foreach ($request->file('material_files') as $materialFile) {
                        $materialName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                            . '.' . $materialFile->getClientOriginalExtension();
                        $materialFile->storeAs('certifications/materials', $materialName, 'public');
                        $materials[] = 'certifications/materials/' . $materialName;
                    }
                }

                // Store material file paths as a string array/json
                $validatedData['material_files'] = $materials;

                $notifier = new RequestNotifierService();
                $editRequest = $notifier->createEditRequest(
                    $certification,
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    [
                        'employee_id' => $employee->id,
                        'method' => 'update',
                    ]
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create certification update request.');
                }

                DB::commit();
                return redirect()->route('employees.certifications.index', $employee->id)
                    ->with('info', 'Certification update request has been sent and is awaiting approval.');
            }

            // 2️⃣ HC → KIRIM REQUEST APPROVAL (checker)
            if ($user->role === 'hc') {
                $payload = $validatedData;

                // Tangani file (upload dulu agar bisa ditinjau di approval)
                if ($request->hasFile('certificate_file')) {
                    $file = $request->file('certificate_file');
                    $fileName = time() . '_cert_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('certifications/temp', $fileName, 'public');
                    $payload['certificate_file'] = 'certifications/temp/' . $fileName;
                }

                $materials = [];
                if ($request->hasFile('material_files')) {
                    foreach ($request->file('material_files') as $materialFile) {
                        $materialName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                            . '.' . $materialFile->getClientOriginalExtension();
                        $materialFile->storeAs('certifications/materials/temp', $materialName, 'public');
                        $materials[] = 'certifications/materials/temp/' . $materialName;
                    }
                }

                $payload['material_files'] = $materials;
                $payload['delete_materials'] = $validatedData['delete_materials'] ?? [];

                // Clone data lama dan isi dengan payload baru
                $tempModel = clone $certification;
                $tempModel->fill([
                    'certification_name' => $payload['certification_name'],
                    'issuer' => $payload['issuer'],
                    'description' => $payload['description'] ?? null,
                    'date_obtained' => $payload['date_obtained'],
                    'expiry_date' => $payload['expiry_date'] ?? null,
                    'cost' => $payload['cost'] ?? null,
                    'certificate_file' => $payload['certificate_file'] ?? $certification->certificate_file,
                ]);

                // Simpan snapshot untuk approval (berelasi aman)
                ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update', [
                    'related_files' => [
                        'new_materials' => $payload['material_files'],
                        'delete_materials' => $payload['delete_materials'],
                    ],
                ]);

                DB::commit();
                return redirect()->route('employees.certifications.index', $employee->id)
                    ->with('success', 'Permintaan pembaruan sertifikasi telah dikirim untuk approval.');
            }

            // 3️⃣ SUPERADMIN → langsung update
            if (!empty($validatedData['delete_materials'])) {
                foreach ($validatedData['delete_materials'] as $materialId) {
                    $material = CertificationMaterial::where('certification_id', $certification->id)
                        ->where('id', $materialId)
                        ->first();
                    if ($material) {
                        Storage::disk('public')->delete('certifications/materials/' . $material->file_path);
                        $material->delete();
                    }
                }
            }

            if ($request->hasFile('certificate_file')) {
                Storage::disk('public')->delete('certifications/main/' . $certification->certificate_file);

                $file = $request->file('certificate_file');
                $fileName = time() . '_cert_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '.' . $file->getClientOriginalExtension();
                $file->storeAs('certifications', $fileName, 'public');
                $validatedData['certificate_file'] = $fileName;
            } else {
                $validatedData['certificate_file'] = $certification->certificate_file;
            }

            $certification->update([
                'certification_name' => $validatedData['certification_name'],
                'issuer' => $validatedData['issuer'],
                'description' => $validatedData['description'] ?? null,
                'date_obtained' => $validatedData['date_obtained'],
                'expiry_date' => $validatedData['expiry_date'] ?? null,
                'cost' => $validatedData['cost'] ?? null,
                'certificate_file' => $validatedData['certificate_file'],
            ]);

            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $filename = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    $materialFile->storeAs('certifications/materials', $filename, 'public');
                    $certification->certificationMaterials()->create([
                        'file_path' => $filename
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('employees.certifications.index', $employee->id)
                ->with('success', 'Certification updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update certification: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, Certification $certification)
    {
        $this->authorizeAccess($employee);

        if ($certification->employee_id !== $employee->id) {
            abort(403, 'Certification is not associated with this employee.');
        }

        $user = Auth::user();
        DB::beginTransaction();
        try {
            // If not superadmin/hc → create delete approval request
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $certification,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create certification deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.certifications.index', $employee->id)
                    ->with('info', 'Certification deletion request has been sent and is awaiting approval.');
            }

            /**
             * 2️⃣ HC → KIRIM REQUEST APPROVAL
             */
            if ($user->role === 'hc') {
                // Capture untuk approval
                $cdr = ApprovalWorkflowService::captureModelChange($user, $certification, 'delete', [
                    'related_files' => [
                        'new_materials' => [],
                        'delete_materials' => $certification->certificationMaterials->pluck('file_path')->toArray(),
                    ],
                ]);

                if (!$cdr) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create certification deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.certifications.index', $employee->id)
                    ->with('success', 'Certification deletion request has been sent for approval.');
            }

            /**
             * 3️⃣ SUPERADMIN → LANGSUNG HAPUS
             */
            // Hapus file certificate
            if (!empty($certification->certificate_file)) {
                Storage::disk('public')->delete($certification->certificate_file);
            }

            // Hapus file materials dan record relasi
            if (method_exists($certification, 'certificationMaterials')) {
                foreach ($certification->certificationMaterials as $material) {
                    Storage::disk('public')->delete('certifications/materials/' . $material->file_path);
                    $material->delete();
                }
            }

            $certification->delete();

            DB::commit();
            return redirect()->route('employees.certifications.index', $employee->id)
                ->with('success', 'Certification deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete certification: ' . $e->getMessage());
        }
    }

    public function destroyMaterial(Employee $employee, Certification $certification, CertificationMaterial $material)
    {
        $this->authorizeAccess($employee);

        if ($certification->employee_id !== $employee->id || $material->certification_id !== $certification->id) {
            abort(403, 'Material file is not associated with this certification.');
        }

        DB::beginTransaction();
        try {
            Storage::disk('public')->delete('certifications/materials/' . $material->file_path);
            $material->delete();

            DB::commit();
            return back()->with('success', 'Material file deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete material file: ' . $e->getMessage());
        }
    }
}