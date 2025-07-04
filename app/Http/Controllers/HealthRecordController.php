<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class HealthRecordController extends Controller
{
    /**
     * Menampilkan form untuk membuat atau mengedit riwayat kesehatan karyawan.
     * Karena relasinya HasOne, form create dan edit adalah sama.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\View\View
     */
    public function edit(Employee $employee)
    {
        // Mengambil data riwayat kesehatan yang sudah ada, atau null jika belum ada.
        $healthRecord = $employee->healthRecord;

        // Mengirim data karyawan dan riwayat kesehatannya ke view
        // View ini akan berfungsi sebagai form 'create' dan 'edit'.
        return view('employees.health-records.form', compact('employee', 'healthRecord'));
    }

    /**
     * Menyimpan atau memperbarui riwayat kesehatan untuk karyawan tertentu.
     * Menggunakan updateOrCreate untuk menangani logika create/update secara otomatis.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeOrUpdate(Request $request, Employee $employee)
    {
        // Validasi input dari form
        $validatedData = $request->validate([
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

        try {
            DB::beginTransaction();

            // Menggunakan updateOrCreate:
            // 1. Mencari HealthRecord dengan employee_id yang cocok.
            // 2. Jika ditemukan, perbarui dengan $validatedData.
            // 3. Jika tidak ditemukan, buat record baru dengan employee_id dan $validatedData.
            $employee->healthRecord()->updateOrCreate(
                ['employee_id' => $employee->id],
                $validatedData
            );

            DB::commit();

            // Redirect kembali ke halaman detail karyawan dengan pesan sukses
            return redirect()->route('employees.show', $employee->id)
                             ->with('success', 'Riwayat kesehatan karyawan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Redirect kembali dengan pesan error
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus riwayat kesehatan milik seorang karyawan.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Employee $employee)
    {
        try {
            // Cek apakah karyawan memiliki riwayat kesehatan
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
