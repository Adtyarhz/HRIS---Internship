<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TrainingHistory;
use App\Models\TrainingMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrainingHistoryController extends Controller
{
    /**
     * Menampilkan daftar riwayat pelatihan untuk karyawan tertentu.
     */
    public function index(Employee $employee)
    {
        $trainingHistories = $employee->trainingHistories()->with('trainingMaterials')->latest('start_date')->get();
        return view('employees.training-histories.index', compact('employee', 'trainingHistories'));
    }

    /**
     * Menampilkan form untuk menambahkan riwayat pelatihan baru.
     */
    public function create(Employee $employee)
    {
        return view('employees.training-histories.create', compact('employee'));
    }

    /**
     * Menyimpan riwayat pelatihan baru ke dalam database.
     */
    public function store(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'training_name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cost' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'certificate_number' => 'required|string|max:50',
            'material_files' => 'nullable|array',
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240' // max 10MB per file
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat record utama training history
            $trainingHistory = $employee->trainingHistories()->create($validatedData);

            // 2. Handle upload file materi (jika ada)
            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $materialFileName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    $materialFile->storeAs('training_materials', $materialFileName, 'public');

                    // Buat record relasi training_materials
                    $trainingHistory->trainingMaterials()->create([
                        'file_path' => $materialFileName
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                            ->with('success', 'Riwayat pelatihan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan riwayat pelatihan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit riwayat pelatihan.
     */
    public function edit(Employee $employee, TrainingHistory $trainingHistory)
    {
        // Eager load materi untuk ditampilkan di form edit
        $trainingHistory->load('trainingMaterials');
        return view('employees.training-histories.edit', compact('employee', 'trainingHistory'));
    }

    /**
     * Memperbarui data riwayat pelatihan di database.
     */
    public function update(Request $request, Employee $employee, TrainingHistory $trainingHistory)
    {
        $validatedData = $request->validate([
            'training_name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cost' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'certificate_number' => 'required|string|max:50',
            'material_files' => 'nullable|array',
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240',
            'delete_materials' => 'nullable|array', // Menambahkan validasi untuk delete_materials
        ]);

        DB::beginTransaction();
        try {
            // 1. Perbarui data teks pada record riwayat pelatihan
            $trainingHistory->update($validatedData);

            // 2. Hapus file materi yang dipilih untuk dihapus
            if (!empty($validatedData['delete_materials'])) {
                foreach ($validatedData['delete_materials'] as $materialId) {
                    $material = TrainingMaterial::where('training_history_id', $trainingHistory->id)
                                                ->where('id', $materialId)
                                                ->first();

                    if ($material) {
                        Storage::disk('public')->delete('training_materials/' . $material->file_path);
                        $material->delete();
                    }
                }
            }

            // 3. Tambah file materi baru (jika ada)
            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $materialFileName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    
                    $materialFile->storeAs('training_materials', $materialFileName, 'public');

                    $trainingHistory->trainingMaterials()->create([
                        'file_path' => $materialFileName
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                            ->with('success', 'Riwayat pelatihan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui riwayat pelatihan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus riwayat pelatihan dan semua file terkait.
     */
    public function destroy(Employee $employee, TrainingHistory $trainingHistory)
    {
        DB::beginTransaction();
        try {
            // 1. Hapus semua file materi dari storage
            foreach ($trainingHistory->trainingMaterials as $material) {
                Storage::delete('public/training_materials/' . $material->file_path);
            }
            
            // 2. Hapus record riwayat pelatihan.
            // Record materi akan terhapus otomatis jika migration di-setup dengan onDelete('cascade').
            $trainingHistory->delete();

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                             ->with('success', 'Riwayat pelatihan berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus riwayat pelatihan.');
        }
    }

    /**
     * Menghapus file materi pelatihan secara individual.
     */
    public function destroyMaterial(Employee $employee, TrainingHistory $trainingHistory, TrainingMaterial $material)
    {
        // Pastikan pelatihan dan materi terkait dengan karyawan
        if ($trainingHistory->employee_id !== $employee->id || $material->training_history_id !== $trainingHistory->id) {
            abort(403, 'File materi tidak terkait dengan pelatihan atau karyawan ini.');
        }

        DB::beginTransaction();
        try {
            // Hapus file dari storage
            if ($material->file_path) {
                Storage::delete('public/training_materials/' . $material->file_path);
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
