<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FamilyDependent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\EmployeeEditRequest;
use Illuminate\Support\Facades\DB;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;

class FamilyDependentController extends Controller
{
    /**
     * Menampilkan daftar tanggungan keluarga untuk karyawan tertentu.
     */
    private function authorizeEmployeeAccess(Employee $employee)
    {
        $user = auth()->user();

        // Jika bukan HC & Superadmin → hanya boleh akses miliknya sendiri
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            if (!$user->employee || $user->employee->id !== $employee->id) {
                abort(403, 'Unauthorized access to this employee\'s family dependents.');
            }
        }
    }

    public function index(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $dependents = $employee->familyDependents()->latest()->get();
        return view('employees.family-dependents.index', compact('employee', 'dependents'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        return view('employees.family-dependents.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'phone_number' => ['required', 'string', 'max:20', 'unique:family_dependents,phone_number', 'regex:/^\+?[0-9]{8,20}$/'],
            'address' => 'required|string',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            // Jika bukan superadmin/hc → buat permintaan edit
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    new FamilyDependent(),
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id,]
                );
                if (!$editRequest) {
                    return back()->with('error', 'Gagal membuat permintaan perubahan data.');
                }
                DB::commit();
                return redirect()->route('employees.family-dependents.index', $employee->id)
                    ->with('info', 'Permintaan penambahan tanggungan keluarga telah dikirim dan menunggu persetujuan.');
            }

            // Jika superadmin/hc → langsung simpan
            $employee->familyDependents()->create($validatedData);
            DB::commit();

            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Data tanggungan keluarga berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, FamilyDependent $familyDependent)
    {
        $this->authorizeEmployeeAccess($employee);

        return view('employees.family-dependents.edit', compact('employee', 'familyDependent'));
    }

    public function update(Request $request, Employee $employee, FamilyDependent $familyDependent)
    {
        $this->authorizeEmployeeAccess($employee);

        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('family_dependents')->ignore($familyDependent->id), 'regex:/^\+?[0-9]{8,20}$/'],
            'address' => 'required|string',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $familyDependent,
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );
                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan perubahan data.');
                }
                DB::commit();
                return redirect()->route('employees.family-dependents.index', $employee->id)
                    ->with('info', 'Permintaan perubahan data telah dikirim dan menunggu persetujuan.');
            }

            // Jika superadmin/hc → langsung update
            $familyDependent->update($validatedData);
            DB::commit();

            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Data tanggungan keluarga berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, FamilyDependent $familyDependent)
    {
        $this->authorizeEmployeeAccess($employee);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            // Jika bukan superadmin/hc → buat permintaan approval hapus
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $familyDependent,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan penghapusan data tanggungan keluarga.');
                }

                DB::commit();
                return redirect()->route('employees.family-dependents.index', $employee->id)
                    ->with('info', 'Permintaan penghapusan tanggungan keluarga telah dikirim dan menunggu persetujuan.');
            }

            // Jika superadmin/hc → langsung hapus data
            $familyDependent->delete();

            DB::commit();
            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Data tanggungan keluarga berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
