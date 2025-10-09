<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Notifications\EmployeeEditRequestNotification;
use App\Services\RequestNotifierService;

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
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $healthRecord ?? new HealthRecord(),
                    $validated,
                    EmployeeEditRequestNotification::class,
                    [
                        'employee_id' => $employee->id,
                        'method' => $healthRecord ? 'update' : 'create',
                    ]
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan perubahan data.');
                }
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

        $user = auth()->user();
        DB::beginTransaction();

        try {
            $healthRecord = $employee->healthRecord;

            if (!$healthRecord) {
                return redirect()->route('employees.show', $employee->id)
                    ->with('info', 'Karyawan ini tidak memiliki data riwayat kesehatan untuk dihapus.');
            }

            // Jika bukan superadmin/hc → buat request approval
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $healthRecord,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan penghapusan data riwayat kesehatan.');
                }

                DB::commit();
                return redirect()->route('employees.show', $employee->id)
                    ->with('info', 'Permintaan penghapusan riwayat kesehatan telah dikirim dan menunggu persetujuan.');
            }

            // Jika superadmin/hc → langsung hapus
            $healthRecord->delete();
            DB::commit();

            return redirect()->route('employees.show', $employee->id)
                ->with('success', 'Riwayat kesehatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.show', $employee->id)
                ->with('error', 'Gagal menghapus data riwayat kesehatan: ' . $e->getMessage());
        }
    }
}
