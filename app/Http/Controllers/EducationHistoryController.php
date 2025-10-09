<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EducationHistory;
use App\Models\EmployeeEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;

class EducationHistoryController extends Controller
{
    private function authorizeEmployeeAccess(Employee $employee)
    {
        $user = auth()->user();

        // Jika bukan HC & Superadmin → hanya boleh akses miliknya sendiri
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            if (!$user->employee || $user->employee->id !== $employee->id) {
                abort(403, 'Unauthorized access to this employee\'s education history.');
            }
        }
    }

    public function index(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $educationHistories = $employee->educationHistory;
        return view('employees.educationhistory.index', compact('employee', 'educationHistories'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);
        return view('employees.educationhistory.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $validated = $request->validate([
            'education_level'      => 'required|in:SD,SMP,SMA,D1,D2,D3,S1,S2,S3',
            'institution_name'     => 'required|string|max:150',
            'institution_address'  => 'required|string',
            'major'                => 'required|string|max:100',
            'start_year'           => 'required|digits:4|integer',
            'end_year'             => 'required|digits:4|integer|gte:start_year',
            'gpa_or_score'         => 'required|numeric|between:0,9999.99',
            'certificate_number'   => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    new EducationHistory(),
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );
                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan pembuatan data pendidikan.');
                }
                DB::commit();
                return redirect()->route('employees.educationhistory.index', $employee)
                                 ->with('info', 'Permintaan penambahan riwayat pendidikan telah dikirim dan menunggu persetujuan.');
            }

            // Superadmin/HC langsung simpan
            $employee->educationHistory()->create($validated);
            DB::commit();

            return redirect()->route('employees.educationhistory.index', $employee)
                             ->with('success', 'Employee Education was Added.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: '.$e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, EducationHistory $educationHistory)
    {
        $this->authorizeEmployeeAccess($employee);
        return view('employees.educationhistory.edit', compact('employee', 'educationHistory'));
    }

    public function update(Request $request, Employee $employee, EducationHistory $educationHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        $validated = $request->validate([
            'education_level'      => 'required|in:SD,SMP,SMA,D1,D2,D3,S1,S2,S3',
            'institution_name'     => 'required|string|max:150',
            'institution_address'  => 'required|string',
            'major'                => 'required|string|max:100',
            'start_year'           => 'required|digits:4|integer',
            'end_year'             => 'required|digits:4|integer|gte:start_year',
            'gpa_or_score'         => 'required|numeric|between:0,9999.99',
            'certificate_number'   => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            if (!in_array($user->role, ['superadmin', 'hc'])) {

                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $educationHistory,
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );
                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan pengubahan data pendidikan.');
                }
                DB::commit();
                return redirect()->route('employees.educationhistory.index', $employee)
                                 ->with('info', 'Permintaan perubahan data telah dikirim dan menunggu persetujuan.');
            }

            // Superadmin/HC langsung update
            $educationHistory->update($validated);
            DB::commit();

            return redirect()->route('employees.educationhistory.index', $employee)
                             ->with('success', 'Employee Education was Updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, EducationHistory $educationHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $educationHistory,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],          
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal membuat permintaan penghapusan data pendidikan.');
                }
                DB::commit();
                return redirect()->route('employees.educationhistory.index', $employee)
                                 ->with('info', 'Permintaan penghapusan riwayat pendidikan telah dikirim dan menunggu persetujuan.');
            }

            // Superadmin/HC langsung hapus
            $educationHistory->delete();
            DB::commit();

            return redirect()->route('employees.educationhistory.index', $employee)
                             ->with('success', 'Employee Education was Deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
