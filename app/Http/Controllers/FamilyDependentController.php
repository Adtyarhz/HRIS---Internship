<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FamilyDependent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;
use App\Services\ApprovalWorkflowService;

class FamilyDependentController extends Controller
{
    /**
     * Ensure the user has access to this employee's family dependents.
     */
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

        $user = Auth::user();
        
        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            $payload = array_merge($validatedData, ['employee_id' => $employee->id]);
            $tempModel = new FamilyDependent($payload);
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create');
            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Permintaan penambahan data tanggungan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            // If not superadmin/hc, create an edit request
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    new FamilyDependent(),
                    $validatedData,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );
                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create family dependent request.');
                }
                DB::commit();
                return redirect()->route('employees.family-dependents.index', $employee->id)
                    ->with('info', 'Family dependent addition request has been sent and is awaiting approval.');
            }

            // If superadmin, directly save
            $employee->familyDependents()->create($validatedData);
            DB::commit();

            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Family dependent data added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save data: ' . $e->getMessage())->withInput();
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

        $user = Auth::user();

        //-- APPROVAL LOGIC START (PERBAIKAN FINAL) --//
        if ($user && $user->role === 'hc') {
            // Buat clone dari model asli untuk menjaga data original
            $tempModel = clone $familyDependent;
            // Isi clone dengan data baru dari validasi
            $tempModel->fill($validatedData);
            
            // Panggil metode public `captureModelChange`
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update');
            
            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Permintaan perubahan data tanggungan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
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
                    return back()->with('error', 'Failed to create family dependent update request.');
                }
                DB::commit();
                return redirect()->route('employees.family-dependents.index', $employee->id)
                    ->with('info', 'Family dependent update request has been sent and is awaiting approval.');
            }

            // If superadmin, directly update
            $familyDependent->update($validatedData);
            DB::commit();

            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Family dependent data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, FamilyDependent $familyDependent)
    {
        $this->authorizeEmployeeAccess($employee);

        $user = Auth::user();

        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            ApprovalWorkflowService::captureModelChange($user, $familyDependent, 'delete');
            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Permintaan penghapusan data tanggungan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            // If not superadmin/hc, create a delete approval request
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
                    return back()->with('error', 'Failed to create family dependent deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.family-dependents.index', $employee->id)
                    ->with('info', 'Family dependent deletion request has been sent and is awaiting approval.');
            }

            // If superadmin, directly delete data
            $familyDependent->delete();

            DB::commit();
            return redirect()->route('employees.family-dependents.index', $employee->id)
                ->with('success', 'Family dependent data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete data: ' . $e->getMessage());
        }
    }
}

