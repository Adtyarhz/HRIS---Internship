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
use App\Models\EmployeeEditRequest;


class CertificationController extends Controller
{
    /**
     * Pastikan user punya akses ke data karyawan ini.
     */
    private function authorizeAccess(Employee $employee)
    {
        $user = Auth::user();

        // Jika bukan HC atau Superadmin, hanya boleh akses data milik sendiri
        if (!in_array($user->role, ['hc', 'superadmin']) && $employee->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses ke data ini.');
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
        'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240'
    ]);

    $user = auth()->user();
    DB::beginTransaction();
    try {
        // Jika bukan HC atau Superadmin → Buat permintaan edit
       if (!in_array($user->role, ['hc', 'superadmin'])) {
    // Simpan file utama ke storage
    if ($request->hasFile('certificate_file')) {
        $mainFile = $request->file('certificate_file');
        $mainFileName = time() . '_cert_' . Str::slug(pathinfo($mainFile->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . $mainFile->getClientOriginalExtension();
        $mainFile->storeAs('certifications', $mainFileName, 'public');

        $validatedData['certificate_file'] = 'certifications/' . $mainFileName;
    }

    // Simpan file materi jika ada
    $materials = [];
    if ($request->hasFile('material_files')) {
        foreach ($request->file('material_files') as $materialFile) {
            $materialName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                . '.' . $materialFile->getClientOriginalExtension();
            $materialFile->storeAs('certifications/materials', $materialName, 'public');

            $materials[] = 'certifications/materials/' . $materialName;
        }
        $validatedData['material_files'] = $materials;
    }

    EmployeeEditRequest::create([
        'employee_id'   => $employee->id,
        'method'        => 'create',
        'model'         => Certification::class,
        'original_data' => [],
        'changed_data'  => $validatedData,
        'status'        => 'waiting',
        'requested_by'  => $user->id,
        'requested_at'  => now(),
    ]);

    DB::commit();
    return redirect()->route('employees.certifications.index', $employee->id)
        ->with('info', 'Permintaan penambahan sertifikasi telah dikirim dan menunggu persetujuan.');
}

        // Proses langsung jika HC/Superadmin
        $mainFile = $request->file('certificate_file');
        $mainFileName = time() . '_cert_' . Str::slug(pathinfo($mainFile->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . $mainFile->getClientOriginalExtension();
        $mainFile->storeAs('certifications', $mainFileName, 'public');

        $certification = Certification::create([
    'employee_id' => $employee->id,
    'certification_name' => $validatedData['certification_name'],
    'issuer' => $validatedData['issuer'],
    'date_obtained' => $validatedData['date_obtained'],
    'expiry_date' => $validatedData['expiry_date'] ?? null,
    'certificate_file' => 'certifications/' . $mainFileName, // ✅ konsisten
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
            ->with('success', 'Sertifikasi dan materi berhasil ditambahkan.');
    } catch (\Exception $e) {
        DB::rollBack();
        Storage::disk('public')->delete('certifications/main/' . ($mainFileName ?? ''));
        return back()->with('error', 'Gagal menyimpan sertifikasi: ' . $e->getMessage())->withInput();
    }
}
    public function edit(Employee $employee, Certification $certification)
    {
        $this->authorizeAccess($employee);

        if ($certification->employee_id !== $employee->id) {
            abort(403, 'Sertifikasi tidak terkait dengan karyawan ini.');
        }

        $certification->load('certificationMaterials');
        return view('employees.certifications.edit', compact('employee', 'certification'));
    }

   public function update(Request $request, Employee $employee, Certification $certification)
{
    $this->authorizeAccess($employee);

    if ($certification->employee_id !== $employee->id) {
        abort(403, 'Sertifikasi tidak terkait dengan karyawan ini.');
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

    $user = auth()->user();
    DB::beginTransaction();

    try {
        // Bagian non-HC/non-superadmin → simpan sebagai permintaan edit
        if (!in_array($user->role, ['hc', 'superadmin'])) {

           // certificate_file
if ($request->hasFile('certificate_file')) {
    $file = $request->file('certificate_file');
    $fileName = time() . '_cert_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        . '.' . $file->getClientOriginalExtension();
    $file->storeAs('certifications/main', $fileName, 'public');
    $validatedData['certificate_file'] = 'certifications/main/' . $fileName;
} else {
    $validatedData['certificate_file'] = $certification->certificate_file ?? null;
}

// material_files
$materialFiles = [];
if ($request->hasFile('material_files')) {
    foreach ($request->file('material_files') as $materialFile) {
        $filename = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . $materialFile->getClientOriginalExtension();
        $materialFile->storeAs('certifications/materials', $filename, 'public');
        $materialFiles[] = 'certifications/materials/' . $filename;
    }
}
$validatedData['material_files'] = $materialFiles;


            EmployeeEditRequest::create([
                'employee_id'   => $employee->id,
                'method'        => 'update',
                'model'         => Certification::class,
                'model_id'      => $certification->id,
                'original_data' => $certification->only([
                    'certification_name', 'issuer', 'description', 'date_obtained',
                    'expiry_date', 'cost', 'certificate_file'
                ]),
                'changed_data'  => $validatedData,
                'status'        => 'waiting',
                'requested_by'  => $user->id,
                'requested_at'  => now(),
            ]);

            DB::commit();
            return redirect()->route('employees.certifications.index', $employee->id)
                ->with('info', 'Permintaan perubahan sertifikasi telah dikirim dan menunggu persetujuan.');
        }

        // Bagian HC/Superadmin → update langsung
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
            ->with('success', 'Sertifikasi berhasil diperbarui.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal memperbarui sertifikasi: ' . $e->getMessage())->withInput();
    }
}
    public function destroy(Employee $employee, Certification $certification)
    {
        $this->authorizeAccess($employee);

        if ($certification->employee_id !== $employee->id) {
            abort(403, 'Sertifikasi tidak terkait dengan karyawan ini.');
        }

        DB::beginTransaction();
        try {
            Storage::disk('public')->delete('certifications/main/' . $certification->certificate_file);

            foreach ($certification->certificationMaterials as $material) {
                Storage::disk('public')->delete('certifications/materials/' . $material->file_path);
                $material->delete();
            }

            $certification->delete();

            DB::commit();
            return redirect()->route('employees.certifications.index', $employee->id)
                ->with('success', 'Sertifikasi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus sertifikasi: ' . $e->getMessage());
        }
    }

    public function destroyMaterial(Employee $employee, Certification $certification, CertificationMaterial $material)
    {
        $this->authorizeAccess($employee);

        if ($certification->employee_id !== $employee->id || $material->certification_id !== $certification->id) {
            abort(403, 'File materi tidak terkait dengan sertifikasi ini.');
        }

        DB::beginTransaction();
        try {
            Storage::disk('public')->delete('certifications/materials/' . $material->file_path);
            $material->delete();

            DB::commit();
            return back()->with('success', 'File materi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus file materi: ' . $e->getMessage());
        }
    }
}
