<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Notifications\EmployeeEditRequestNotification;
use App\Services\RequestNotifierService;
use Illuminate\Support\Facades\Auth;

class HealthRecordController extends Controller
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

    /**
     * Display the form for creating or editing an employee's health record.
     * Since the relationship is HasOne, the create and edit form is the same.
     */
    public function edit(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        // Retrieve existing health record, or null if none exists
        $healthRecord = $employee->healthRecord;

        return view('employees.health-records.form', compact('employee', 'healthRecord'));
    }

    /**
     * Store or update the health record for a specific employee.
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

        $user = Auth::user();
        DB::beginTransaction();

        try {
            $healthRecord = $employee->healthRecord;

            // If not superadmin/hc, create an edit request
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
                    return back()->with('error', 'Failed to create health record request.');
                }
                DB::commit();
                $message = $healthRecord
                ? 'Health record creation request has been sent and is awaiting approval.'
                : 'Health record update request has been sent and is awaiting approval.';
                
                return redirect()->back()->with('info', $message);
            }

            // If superadmin/hc, directly update or create
            $employee->healthRecord()->updateOrCreate(
                ['employee_id' => $employee->id],
                $validated
            );

            DB::commit();

            return redirect()->back()->with('success', 'Health record saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error occurred while saving data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete the health record of an employee.
     */
    public function destroy(Employee $employee)
    {
        $this->authorizeEmployeeAccess($employee);

        $user = Auth::user();
        DB::beginTransaction();

        try {
            $healthRecord = $employee->healthRecord;

            if (!$healthRecord) {
                return redirect()->route('employees.show', $employee->id)
                    ->with('info', 'This employee has no health record to delete.');
            }

            // If not superadmin/hc, create a delete approval request
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
                    return back()->with('error', 'Failed to create health record deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.show', $employee->id)
                    ->with('info', 'Health record deletion request has been sent and is awaiting approval.');
            }

            // If superadmin/hc, directly delete
            $healthRecord->delete();
            DB::commit();

            return redirect()->route('employees.show', $employee->id)
                ->with('success', 'Health record deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.show', $employee->id)
                ->with('error', 'Failed to delete health record: ' . $e->getMessage());
        }
    }
}