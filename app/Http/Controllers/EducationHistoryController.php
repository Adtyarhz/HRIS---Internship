<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EducationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;
use Illuminate\Support\Facades\Auth;
use App\Services\ApprovalWorkflowService;

class EducationHistoryController extends Controller
{
    private function authorizeEmployeeAccess(Employee $employee)
    {
        $user = Auth::user();

        // If not HC or Superadmin, only allow access to own data
        if (!in_array($user->role, ['superadmin', 'hc'])) {
            if (!$user->employee || $user->employee->id !== $employee->id) {
                abort(403, 'You do not have permission to access this data.');
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

        $user = Auth::user();

        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            $payload = array_merge($validated, ['employee_id' => $employee->id]);
            $tempModel = new EducationHistory($payload);
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create');
            return redirect()->route('employees.educationhistory.index', $employee)
                ->with('success', 'Permintaan penambahan riwayat pendidikan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//
        
        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
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
                    return back()->with('error', 'Failed to create education history request.');
                }
                DB::commit();
                return redirect()->route('employees.educationhistory.index', $employee)
                                        ->with('info', 'Education history addition request has been sent and is awaiting approval.');
            }

            // Superadmin directly save
            $employee->educationHistory()->create($validated);
            DB::commit();

            return redirect()->route('employees.educationhistory.index', $employee)
                                        ->with('success', 'Education history added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save data: '.$e->getMessage())->withInput();
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

        $user = Auth::user();

        //-- APPROVAL LOGIC START (PERBAIKAN KONSISTENSI) --//
        if ($user && $user->role === 'hc') {
            // Buat clone dari model asli untuk menjaga data original
            $tempModel = clone $educationHistory;
            // Isi clone dengan data baru dari validasi
            $tempModel->fill($validated);

            // Panggil metode public `captureModelChange`
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update');
            
            return redirect()->route('employees.educationhistory.index', $employee)
                ->with('success', 'Permintaan perubahan riwayat pendidikan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
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
                    return back()->with('error', 'Failed to create education history update request.');
                }
                DB::commit();
                return redirect()->route('employees.educationhistory.index', $employee)
                                        ->with('info', 'Education history update request has been sent and is awaiting approval.');
            }

            // Superadmin directly update
            $educationHistory->update($validated);
            DB::commit();

            return redirect()->route('employees.educationhistory.index', $employee)
                                        ->with('success', 'Education history updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update data: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, EducationHistory $educationHistory)
    {
        $this->authorizeEmployeeAccess($employee);

        $user = Auth::user();

        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            ApprovalWorkflowService::captureModelChange($user, $educationHistory, 'delete');
            return redirect()->route('employees.educationhistory.index', $employee)
                ->with('success', 'Permintaan penghapusan riwayat pendidikan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//
        
        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
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
                    return back()->with('error', 'Failed to create education history deletion request.');
                }
                DB::commit();
                return redirect()->route('employees.educationhistory.index', $employee)
                                        ->with('info', 'Education history deletion request has been sent and is awaiting approval.');
            }

            // Superadmin directly delete
            $educationHistory->delete();
            DB::commit();

            return redirect()->route('employees.educationhistory.index', $employee)
                                        ->with('success', 'Education history deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete data: '.$e->getMessage());
        }
    }
}

