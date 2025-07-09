<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Certification;
use App\Models\CertificationMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CertificationController extends Controller
{
    /**
     * Menampilkan daftar sertifikasi untuk karyawan tertentu.
     */
    public function index(Employee $employee)
    {
        // Eager load relasi untuk efisiensi query
        $certifications = $employee->certifications()->with('certificationMaterials')->latest('date_obtained')->get();
        return view('employees.certifications.index', compact('employee', 'certifications'));
    }

    /**
     * Menampilkan form untuk menambahkan sertifikasi baru.
     */
    public function create(Employee $employee)
    {
        return view('employees.certifications.create', compact('employee'));
    }

    /**
     * Menyimpan sertifikasi baru ke dalam database dengan logika file yang baru.
     */
    public function store(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'certification_name' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_obtained' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:date_obtained',
            'cost' => 'nullable|numeric|min:0',
            // Validasi untuk file sertifikat utama (wajib, tunggal)
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB
            // Validasi untuk file materi (opsional, jamak/array)
            'material_files' => 'nullable|array|max:10', // Batas maksimum 10 file
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240' // max 10MB per file
        ]);

        DB::beginTransaction();
        try {
            // 1. Handle unggahan file sertifikat utama
            $mainCertificateFileName = null;
            if ($request->hasFile('certificate_file')) {
                $file = $request->file('certificate_file');
                $mainCertificateFileName = time() . '_cert_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '.' . $file->getClientOriginalExtension();
                $storedPath = $file->storeAs('certifications/main', $mainCertificateFileName, 'public');
            }

            // 2. Buat record sertifikasi utama
            $certificationData = [
                'employee_id' => $employee->id,
                'certification_name' => $validatedData['certification_name'],
                'issuer' => $validatedData['issuer'],
                'description' => $validatedData['description'] ?? null,
                'date_obtained' => $validatedData['date_obtained'],
                'expiry_date' => $validatedData['expiry_date'] ?? null,
                'cost' => $validatedData['cost'] ?? null,
                'certificate_file' => $mainCertificateFileName,
            ];

            $certification = Certification::create($certificationData);

            // 3. Handle unggahan file-file materi pendukung (jika ada)
            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $materialFileName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    $materialStoredPath = $materialFile->storeAs('certifications/materials', $materialFileName, 'public');

                    $certification->certificationMaterials()->create([
                        'file_path' => $materialFileName // hanya simpan nama file
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('employees.certifications.index', $employee->id)
                ->with('success', 'Sertifikasi dan materi pendukung berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus file sertifikat utama jika sudah terunggah
            if (!empty($mainCertificateFileName)) {
                Storage::disk('public')->delete('certifications/main/' . $mainCertificateFileName);
            }
            return back()->with('error', 'Gagal menyimpan sertifikasi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit sertifikasi yang ada.
     */
    public function edit(Employee $employee, Certification $certification)
    {
        // Pastikan sertifikasi milik karyawan
        if ($certification->employee_id !== $employee->id) {
            abort(403, 'Sertifikasi tidak terkait dengan karyawan ini.');
        }

        // Eager load materi untuk ditampilkan di form edit
        $certification->load('certificationMaterials');
        return view('employees.certifications.edit', compact('employee', 'certification'));
    }

    /**
     * Memperbarui data sertifikasi di database.
     */
    public function update(Request $request, Employee $employee, Certification $certification)
    {
        // Pastikan sertifikasi milik karyawan
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
            // File utama sekarang opsional saat update
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            // File materi baru juga opsional
            'material_files' => 'nullable|array|max:10', // Batas maksimum 10 file
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240',
            // ID file materi yang akan dihapus (opsional)
            'delete_materials' => 'nullable|array',
            'delete_materials.*' => 'exists:certification_materials,id'
        ]);

        DB::beginTransaction();
        try {
            // 1. Hapus file materi yang dipilih
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

            // 2. Perbarui file sertifikat utama (jika diunggah baru)
            if ($request->hasFile('certificate_file')) {
                // Hapus file lama jika ada
                if ($certification->certificate_file) {
                    Storage::disk('public')->delete('certifications/main/' . $certification->certificate_file);
                }

                // Upload file baru dengan nama aman
                $file = $request->file('certificate_file');
                $fileName = time() . '_cert_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '.' . $file->getClientOriginalExtension();
                $file->storeAs('certifications/main', $fileName, 'public');

                $validatedData['certificate_file'] = $fileName;
            } else {
                // Pertahankan file lama jika tidak diganti
                $validatedData['certificate_file'] = $certification->certificate_file;
            }

            // 3. Update data sertifikasi
            $certification->update([
                'certification_name' => $validatedData['certification_name'],
                'issuer' => $validatedData['issuer'],
                'description' => $validatedData['description'] ?? null,
                'date_obtained' => $validatedData['date_obtained'],
                'expiry_date' => $validatedData['expiry_date'] ?? null,
                'cost' => $validatedData['cost'] ?? null,
                'certificate_file' => $validatedData['certificate_file'],
            ]);

            // 4. Tambah file materi baru jika ada
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
                ->with('success', 'Sertifikasi dan materi pendukung berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui sertifikasi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus sertifikasi, file utama, dan semua file materi terkait.
     */
    public function destroy(Employee $employee, Certification $certification)
    {
        // Pastikan sertifikasi milik karyawan
        if ($certification->employee_id !== $employee->id) {
            abort(403, 'Sertifikasi tidak terkait dengan karyawan ini.');
        }

        DB::beginTransaction();
        try {
            // 1. Hapus file sertifikat utama dari storage
            if ($certification->certificate_file) {
                Storage::delete('public/certifications/main/' . $certification->certificate_file);
            }

            // 2. Hapus semua file materi dari storage
            foreach ($certification->certificationMaterials as $material) {
                Storage::delete('public/certifications/materials/' . $material->file_path);
                $material->delete(); // Hapus manual jika tidak ada cascade delete
            }

            // 3. Hapus record sertifikasi
            $certification->delete();

            DB::commit();
            return redirect()->route('employees.certifications.index', $employee->id)
                ->with('success', 'Sertifikasi dan semua file terkait berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus sertifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus file materi sertifikasi secara individual.
     */
    public function destroyMaterial(Employee $employee, Certification $certification, CertificationMaterial $material)
    {
        // Pastikan sertifikasi dan materi terkait dengan karyawan
        if ($certification->employee_id !== $employee->id || $material->certification_id !== $certification->id) {
            abort(403, 'File materi tidak terkait dengan sertifikasi atau karyawan ini.');
        }

        DB::beginTransaction();
        try {
            // Hapus file dari storage
            if ($material->file_path) {
                Storage::delete('public/certifications/materials/' . $material->file_path);
            }

            // Hapus record dari database
            $material->delete();

            DB::commit();
            return back()->with('success', 'File materi berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus file materi: ' . $e->getMessage());
        }
    }
}