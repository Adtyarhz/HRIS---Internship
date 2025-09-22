<?php

namespace App\Http\Controllers;

use App\Models\CareerHistory;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Division;
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

        $divisions = Division::orderBy('name')->get();
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
        $divisions = Division::orderBy('name')->get()->pluck('name', 'id');
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

        try {
            DB::beginTransaction();

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
        $divisions = Division::orderBy('name')->get()->pluck('name', 'id');
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

        try {
            DB::beginTransaction();

            $data = $request->only([
                'position_id',
                'division_id',
                'employee_type',
                'start_date',
                'end_date',
                'type',
                'notes'
            ]);

            // Cek apakah ada perubahan pada position_id, division_id, atau employee_type
            $hasChanges = $careerHistory->position_id != $data['position_id'] ||
                $careerHistory->division_id != $data['division_id'] ||
                $careerHistory->employee_type != $data['employee_type'];

            // Jika CareerHistory ini aktif (end_date null) dan ada perubahan,
            // tutup CareerHistory ini dan buat yang baru
            if (is_null($careerHistory->end_date) && $hasChanges) {
                $careerHistory->update([
                    'end_date' => Carbon::today(),
                ]);

                // Buat CareerHistory baru dengan data baru
                CareerHistory::create([
                    'employee_id' => $employee->id,
                    'position_id' => $data['position_id'],
                    'division_id' => $data['division_id'],
                    'employee_type' => $data['employee_type'],
                    'start_date' => Carbon::today(),
                    'end_date' => null,
                    'type' => $data['type'], // Gunakan tipe dari input
                    'notes' => $data['notes'],
                ]);

                // Perbarui data Employee berdasarkan CareerHistory baru
                $employee->update([
                    'position_id' => $data['position_id'],
                    'division_id' => $data['division_id'],
                    'employee_type' => $data['employee_type'],
                ]);
            } else {
                // Jika tidak ada perubahan atau CareerHistory tidak aktif, update saja
                $careerHistory->update($data);

                // Jika CareerHistory ini aktif (end_date null), perbarui data Employee
                if (is_null($careerHistory->end_date)) {
                    $employee->update([
                        'position_id' => $data['position_id'],
                        'division_id' => $data['division_id'],
                        'employee_type' => $data['employee_type'],
                    ]);
                }
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

            // Jika CareerHistory yang dihapus adalah aktif (end_date null),
            // perbarui Employee ke CareerHistory aktif terbaru (jika ada)
            if (is_null($careerHistory->end_date)) {
                $latestCareerHistory = CareerHistory::where('employee_id', $employee->id)
                    ->whereNotNull('end_date')
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($latestCareerHistory) {
                    $employee->update([
                        'position_id' => $latestCareerHistory->position_id,
                        'division_id' => $latestCareerHistory->division_id,
                        'employee_type' => $latestCareerHistory->employee_type,
                    ]);
                } else {
                    // Jika tidak ada CareerHistory lain, set ke null
                    $employee->update([
                        'position_id' => null,
                        'division_id' => null,
                        'employee_type' => $employee->employee_type, // Pertahankan employee_type
                    ]);
                }
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