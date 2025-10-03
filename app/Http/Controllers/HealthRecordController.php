<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class HealthRecordController extends Controller
{
    private function authorizeEmployeeAccess(Employee $employee)
    {
        $user = auth()->user();

        // Jika bukan HC & Superadmin → hanya boleh akses miliknya sendiri
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            if (!$user->employee || $user->employee->id !== $employee->id) {
                abort(403, 'Unauthorized access to this employee\'s health record.');
            }
        }
    }

    /**
     * Menampilkan form untuk membuat atau mengedit riwayat kesehatan karyawan.
     * Karena relasinya HasOne, form create dan edit adalah sama.
     */
    public function edit(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        // Mengambil data riwayat kesehatan yang sudah ada, atau null jika belum ada.
        $healthRecord = $employee->healthRecord;

        return view('employees.health-records.form', compact('employee', 'healthRecord'));
    }

    /**
     * Menyimpan atau memperbarui riwayat kesehatan untuk karyawan tertentu.
     */
    public function storeOrUpdate(Request $request, Employee $employee)
{
    $this->authorizeEmployeeAccess($employee);

    $validated = $request->validate([
        'height' => 'nullable|numeric|min:0',
        'weight' => 'nullable|numeric|min:0',
        'blood_type' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O', 'Tidak Tahu'])],
        'known_allergies' => 'nullable|string',
        'chronic_diseases' => 'nullable|string',
        'last_checkup_date' => 'nullable|date',
        'checkup_loc' => 'nullable|string|max:255',
        'price_last_checkup' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
    ]);

    $user = auth()->user();
    DB::beginTransaction();

    try {
        $healthRecord = $employee->healthRecord;

        // Jika bukan superadmin/hc → buat request edit
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            \App\Models\EmployeeEditRequest::create([
                'employee_id'   => $employee->id,
                'method'        => $healthRecord ? 'update' : 'create',
                'model'         => HealthRecord::class,
                'model_id'      => $healthRecord?->id,
                'original_data' => $healthRecord ? $healthRecord->only(array_keys($validated)) : [],
                'changed_data'  => $validated,
                'status'        => 'waiting',
                'requested_by'  => $user->id,
                'requested_at'  => now(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('info', 'Permintaan perubahan data telah dikirim dan menunggu persetujuan.');
        }

        // Jika superadmin/hc → langsung update atau buat
        $employee->healthRecord()->updateOrCreate(
            ['employee_id' => $employee->id],
            $validated
        );

        DB::commit();

        return redirect()->back()->with('success', 'Riwayat kesehatan karyawan berhasil disimpan.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
    }
}

    /**
     * Menghapus riwayat kesehatan milik seorang karyawan.
     */
    public function destroy(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        try {
            if ($employee->healthRecord) {
                $employee->healthRecord->delete();
                return redirect()->route('employees.show', $employee->id)
                                 ->with('success', 'Riwayat kesehatan berhasil dihapus.');
            }

            return redirect()->route('employees.show', $employee->id)
                             ->with('info', 'Karyawan ini tidak memiliki data riwayat kesehatan untuk dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('employees.show', $employee->id)
                             ->with('error', 'Gagal menghapus data riwayat kesehatan.');
        }
    }
}
