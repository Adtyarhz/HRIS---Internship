<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TrainingHistory;
use App\Models\TrainingMaterial;
use App\Models\EmployeeEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrainingHistoryController extends Controller
{
    private function authorizeEmployeeAccess(Employee $employee)
    {
        $user = auth()->user();

        // Jika bukan HC & Superadmin → hanya boleh akses miliknya sendiri
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            if (!$user->employee || $user->employee->id !== $employee->id) {
                abort(403, 'Unauthorized access to this employee\'s training history.');
            }
        }
    }

    public function index(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $trainingHistories = $employee->trainingHistories()->with('trainingMaterials')->latest('start_date')->get();
        return view('employees.training-histories.index', compact('employee', 'trainingHistories'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        return view('employees.training-histories.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

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
            'material_files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,zip|max:10240'
        ]);

        $user = auth()->user();
        DB::beginTransaction();
        try {
            // Upload file materi terlebih dahulu (jika ada)
            $storedFiles = [];
            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $materialFileName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    $materialFile->storeAs('training_materials', $materialFileName, 'public');
                    $storedFiles[] = $materialFileName;
                }
            }

            // Kalau bukan superadmin / hc, buat request edit
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $validatedData['material_files_uploaded'] = $storedFiles;

                EmployeeEditRequest::create([
                    'employee_id'   => $employee->id,
                    'method'        => 'create',
                    'model'         => TrainingHistory::class,
                    'model_id'      => null,
                    'original_data' => null,
                    'changed_data'  => $validatedData,
                    'status'        => 'waiting',
                    'requested_by'  => $user->id,
                    'requested_at'  => now(),
                ]);

                DB::commit();
                return redirect()->route('employees.training-histories.index', $employee->id)
                                ->with('info', 'Permintaan penambahan riwayat pelatihan telah dikirim dan menunggu persetujuan.');
            }

            // Superadmin/HC langsung simpan
            $trainingHistory = $employee->trainingHistories()->create($validatedData);
            foreach ($storedFiles as $filename) {
                $trainingHistory->trainingMaterials()->create(['file_path' => $filename]);
            }

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                            ->with('success', 'Riwayat pelatihan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan riwayat pelatihan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, TrainingHistory $trainingHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        $trainingHistory->load('trainingMaterials');
        return view('employees.training-histories.edit', compact('employee', 'trainingHistory'));
    }

    public function update(Request $request, Employee $employee, TrainingHistory $trainingHistory)
    {
        $this->authorizeEmployeeAccess($employee);

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
            'delete_materials' => 'nullable|array',
        ]);

        $user = auth()->user();
        DB::beginTransaction();
        try {
            $storedFiles = [];
            if ($request->hasFile('material_files')) {
                foreach ($request->file('material_files') as $materialFile) {
                    $materialFileName = time() . '_mat_' . Str::slug(pathinfo($materialFile->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $materialFile->getClientOriginalExtension();
                    $materialFile->storeAs('training_materials', $materialFileName, 'public');
                    $storedFiles[] = $materialFileName;
                }
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $originalData = $trainingHistory->only(array_keys($validatedData));

                $validatedData['material_files_uploaded'] = $storedFiles;
                $validatedData['delete_materials'] = $validatedData['delete_materials'] ?? [];

                EmployeeEditRequest::create([
                    'employee_id'   => $employee->id,
                    'method'        => 'update',
                    'model'         => TrainingHistory::class,
                    'model_id'      => $trainingHistory->id,
                    'original_data' => $originalData,
                    'changed_data'  => $validatedData,
                    'status'        => 'waiting',
                    'requested_by'  => $user->id,
                    'requested_at'  => now(),
                ]);

                DB::commit();
                return redirect()->route('employees.training-histories.index', $employee->id)
                                ->with('info', 'Permintaan perubahan riwayat pelatihan telah dikirim dan menunggu persetujuan.');
            }

            $trainingHistory->update($validatedData);

            // Hapus file yang dipilih
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

            // Upload file baru
            foreach ($storedFiles as $filename) {
                $trainingHistory->trainingMaterials()->create(['file_path' => $filename]);
            }

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                            ->with('success', 'Riwayat pelatihan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui riwayat pelatihan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, TrainingHistory $trainingHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        DB::beginTransaction();
        try {
            foreach ($trainingHistory->trainingMaterials as $material) {
                Storage::delete('public/training_materials/' . $material->file_path);
            }
            $trainingHistory->delete();

            DB::commit();
            return redirect()->route('employees.training-histories.index', $employee->id)
                             ->with('success', 'Riwayat pelatihan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus riwayat pelatihan.');
        }
    }

    public function destroyMaterial(Employee $employee, TrainingHistory $trainingHistory, TrainingMaterial $material)
    {
        $this->authorizeEmployeeAccess($employee);

        if ($trainingHistory->employee_id !== $employee->id || $material->training_history_id !== $trainingHistory->id) {
            abort(403, 'File materi tidak terkait dengan pelatihan atau karyawan ini.');
        }

        DB::beginTransaction();
        try {
            if ($material->file_path) {
                Storage::delete('public/training_materials/' . $material->file_path);
            }
            $material->delete();

            DB::commit();
            return back()->with('success', 'File materi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus file materi: ' . $e->getMessage());
        }
    }
}
