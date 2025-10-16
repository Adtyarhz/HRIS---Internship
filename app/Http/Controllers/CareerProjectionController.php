<?php

namespace App\Http\Controllers;

use App\Models\CareerProjection;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CareerProjectionController extends Controller
{
    /**
     * Validasi akses pengguna terhadap data karyawan.
     */
    private function authorizeAccess(Employee $employee)
    {
        $user = Auth::user();

        // Superadmin dan HC bisa akses semua
        if (in_array($user->role, ['superadmin', 'hc'])) {
            return;
        }

        // Manager bisa akses karyawan dalam divisinya
        if ($user->role === 'manager' && $user->employee && $user->employee->division_id === $employee->division_id) {
            return;
        }

        // Section Head bisa akses jika dalam divisi yang sama dan tidak ada manager
        if ($user->role === 'section_head' && $user->employee && $user->employee->division_id === $employee->division_id) {
            $managerExists = \App\Models\User::where('role', 'manager')
                ->whereHas('employee', fn($q) => $q->where('division_id', $employee->division_id))
                ->exists();

            if (!$managerExists) {
                return;
            }
        }

        // Karyawan bisa akses miliknya sendiri
        if ($employee->user_id === $user->id) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses untuk data ini.');
    }

    /**
     * Tampilkan daftar Career Projection karyawan.
     */
    public function index(Employee $employee)
    {
        $this->authorizeAccess($employee);

        $careerProjections = CareerProjection::where('employee_id', $employee->id)
            ->with(['employee', 'projectedPosition', 'creator'])
            ->orderBy('timeline')
            ->get();

        return view('career-path.career_projections.index', compact('careerProjections', 'employee'));
    }

    /**
     * Form untuk membuat Career Projection baru.
     */
    public function create(Employee $employee)
    {
        $this->authorizeAccess($employee);

        $positions = Position::orderBy('title')->pluck('title', 'id');
        $timelines = ['1 Tahun', '3 Tahun', '5 Tahun'];

        return view('career-path.career_projections.form', compact('employee', 'positions', 'timelines'));
    }

    /**
     * Simpan Career Projection baru.
     */
    public function store(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $validator = Validator::make($request->all(), [
            'projected_position_id' => 'required|exists:positions,id',
            'timeline' => 'required|in:1 Tahun,3 Tahun,5 Tahun',
            'status' => 'required|in:Direncanakan,Disetujui,Tercapai,Dibatalkan',
            'readiness_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        CareerProjection::create([
            'employee_id' => $employee->id,
            'projected_position_id' => $request->projected_position_id,
            'timeline' => $request->timeline,
            'status' => $request->status,
            'readiness_notes' => $request->readiness_notes,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('employees.showCareer', $employee)
            ->with('success', 'Career projection berhasil dibuat.');
    }

    /**
     * Edit Career Projection.
     */
    public function edit(Employee $employee, CareerProjection $careerProjection)
    {
        $this->authorizeAccess($employee);

        if ($careerProjection->employee_id !== $employee->id) {
            abort(404);
        }

        $positions = Position::orderBy('title')->pluck('title', 'id');
        $timelines = ['1 Tahun', '3 Tahun', '5 Tahun'];

        return view('career-path.career_projections.form', compact('employee', 'careerProjection', 'positions', 'timelines'));
    }

    /**
     * Update Career Projection.
     */
    public function update(Request $request, Employee $employee, CareerProjection $careerProjection)
    {
        $this->authorizeAccess($employee);

        if ($careerProjection->employee_id !== $employee->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'projected_position_id' => 'required|exists:positions,id',
            'timeline' => 'required|in:1 Tahun,3 Tahun,5 Tahun',
            'status' => 'required|in:Direncanakan,Disetujui,Tercapai,Dibatalkan',
            'readiness_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $careerProjection->update([
            'projected_position_id' => $request->projected_position_id,
            'timeline' => $request->timeline,
            'status' => $request->status,
            'readiness_notes' => $request->readiness_notes,
        ]);

        return redirect()->route('employees.showCareer', $employee)
            ->with('success', 'Career projection berhasil diperbarui.');
    }

    /**
     * Hapus Career Projection.
     */
    public function destroy(Employee $employee, CareerProjection $careerProjection)
    {
        $this->authorizeAccess($employee);

        if ($careerProjection->employee_id !== $employee->id) {
            abort(404);
        }

        $careerProjection->delete();

        return redirect()->route('employees.showCareer', $employee)
            ->with('success', 'Career projection berhasil dihapus.');
    }
}
