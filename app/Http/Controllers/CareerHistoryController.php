<?php

namespace App\Http\Controllers;

use App\Models\CareerHistory;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Division;
use App\Models\EmployeeEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CareerHistoryController extends Controller
{
    /**
     * Display a listing of the career histories.
     */
    public function index(Employee $employee)
    {
        $user = auth()->user();

        // Bukan superadmin/hc -> hanya boleh buat untuk dirinya sendiri
        if (!in_array($user->role, ['superadmin', 'hc', 'direksi']) && $employee->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk menambah riwayat karir ini.');
        }
        $careerHistories = CareerHistory::where('employee_id', $employee->id)
            ->with(['position', 'division'])
            ->orderBy('id')
            ->get();

        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get(); // except N/A
        $positions = Position::orderBy('title')->get();

        return view('career-path.career_histories.index', compact('careerHistories', 'employee', 'divisions', 'positions'));
    }

    /**
     * Show the form for creating a new career history.
     */
    public function create(Employee $employee)
    {
        $user = auth()->user();

        // Bukan superadmin/hc -> hanya boleh buat untuk dirinya sendiri
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk menambah riwayat karir ini.');
        }
        $positions = Position::orderBy('title')->get()->pluck('title', 'id');
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get()->pluck('name', 'id');
        return view('career-path.career_histories.create', compact('employee', 'positions', 'divisions'));
    }

    /**
     * Store a newly created career history in storage.
     */
    public function store(Request $request, Employee $employee)
    {
        $user = auth()->user();

        // Bukan superadmin/hc -> hanya boleh buat untuk dirinya sendiri
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk menambah riwayat karir ini.');
        }
        $validator = Validator::make($request->all(), [
            'position_id' => 'required|exists:positions,id',
            'division_id' => 'required|exists:divisions,id',
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:Promosi,Mutasi,Demosi,Awal Masuk',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'position_id',
            'division_id',
            'employee_type',
            'start_date',
            'end_date',
            'type',
            'notes'
        ]);
        $data['employee_id'] = $employee->id;

        try {
            DB::beginTransaction();

            // Jika bukan superadmin / hc → buat request approval
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                EmployeeEditRequest::create([
                    'employee_id' => $employee->id,
                    'method' => 'store',
                    'model' => CareerHistory::class,
                    'model_id' => null, // karena create baru
                    'original_data' => null,
                    'changed_data' => $data,
                    'status' => 'waiting',
                    'requested_by' => $user->id,
                    'requested_at' => now(),
                ]);

                DB::commit();
                return redirect()->route('employees.showCareer', $employee)
                    ->with('info', 'Permintaan penambahan riwayat karir telah dikirim dan menunggu persetujuan.');
            }

            // === Logika lama: langsung simpan ke CareerHistory ===

            // Tutup CareerHistory aktif sebelumnya (jika ada)
            $activeCareerHistory = CareerHistory::where('employee_id', $employee->id)
                ->whereNull('end_date')
                ->first();

            if ($activeCareerHistory) {
                $activeCareerHistory->update([
                    'end_date' => Carbon::today(),
                ]);
            }

            CareerHistory::create($data);

            // Perbarui data Employee berdasarkan CareerHistory terbaru
            $employee->update([
                'position_id' => $data['position_id'],
                'division_id' => $data['division_id'],
                'employee_type' => $data['employee_type'],
            ]);

            DB::commit();
            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Riwayat karir berhasil ditambahkan dan data karyawan diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified career history.
     */
    public function edit(Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }
        $user = auth()->user();
        // Bukan superadmin/hc -> hanya boleh edit miliknya sendiri
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit riwayat karir ini.');
        }

        $positions = Position::orderBy('title')->get()->pluck('title', 'id');
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get()->pluck('name', 'id');
        return view('career-path.career_histories.edit', compact('employee', 'careerHistory', 'positions', 'divisions'));
    }

    /**
     * Update the specified career history in storage.
     */
    public function update(Request $request, Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }

        $user = auth()->user();
        // Bukan superadmin/hc -> hanya boleh update miliknya sendiri
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui riwayat karir ini.');
        }

        $validator = Validator::make($request->all(), [
            'position_id' => 'required|exists:positions,id',
            'division_id' => 'required|exists:divisions,id',
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:Promosi,Mutasi,Demosi,Awal Masuk',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'position_id',
            'division_id',
            'employee_type',
            'start_date',
            'end_date',
            'type',
            'notes'
        ]);

        try {
            DB::beginTransaction();

            // Jika bukan superadmin / hc → buat request approval
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                EmployeeEditRequest::create([
                    'employee_id' => $employee->id,
                    'method' => 'update',
                    'model' => CareerHistory::class,
                    'model_id' => $careerHistory->id,
                    'original_data' => $careerHistory->toArray(),
                    'changed_data' => $data,
                    'status' => 'waiting',
                    'requested_by' => $user->id,
                    'requested_at' => now(),
                ]);

                DB::commit();
                return redirect()->route('employees.showCareer', $employee)
                    ->with('info', 'Permintaan perubahan riwayat karir telah dikirim dan menunggu persetujuan.');
            }

            // === Logika update CareerHistory ===
            if (is_null($careerHistory->end_date)) {
                // CASE: Career History AKTIF

                $careerHistory->update($data);

                if (is_null($data['end_date'])) {
                    // Masih aktif → update Employee
                    $employee->update([
                        'position_id' => $data['position_id'],
                        'division_id' => $data['division_id'],
                        'employee_type' => $data['employee_type'],
                    ]);
                } else {
                    // Jika end_date diisi → cek apakah sudah lewat
                    if (Carbon::now()->greaterThan(Carbon::parse($data['end_date']))) {
                        // Hapus data jabatan di employee
                        $employee->update([
                            'position_id' => null,
                            'division_id' => null,
                            'employee_type' => null,
                        ]);
                    }
                }
            } else {
                // CASE: Career History SUDAH BERAKHIR
                $newEndDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

                if (is_null($newEndDate)) {
                    return back()->with('error', 'Tanggal berakhir riwayat yang sudah selesai tidak boleh null.')->withInput();
                }

                if ($newEndDate->greaterThan(Carbon::today())) {
                    return back()->with('error', 'Tanggal berakhir riwayat yang sudah selesai tidak boleh melewati hari ini.')->withInput();
                }

                $careerHistory->update($data);
            }

            DB::commit();
            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Riwayat karir berhasil diperbarui dan data karyawan diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified career history from storage.
     */
    public function destroy(Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }

        $user = auth()->user();
        // Bukan superadmin/hc -> hanya boleh hapus miliknya sendiri
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus riwayat karir ini.');
        }
        try {
            DB::beginTransaction();

            // Jika CareerHistory yang dihapus adalah aktif (end_date null)
            if (is_null($careerHistory->end_date)) {
                // Hapus juga data jabatan employee
                $employee->update([
                    'position_id' => null,
                    'division_id' => null,
                    'employee_type' => null,
                ]);
            }

            $careerHistory->delete();

            DB::commit();
            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Riwayat karir berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.showCareer', $employee)
                ->with('error', 'Gagal menghapus riwayat karir: ' . $e->getMessage());
        }
    }
}