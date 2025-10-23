<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;
use App\Services\ApprovalWorkflowService;

class InsuranceController extends Controller
{
    public function index(Employee $employee)
    {
        $this->authorizeAccess($employee);

        $insurances = $employee->insurance()->orderBy('id', 'asc')->get();

        return view('employees.insurance.index', compact('employee', 'insurances'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeAccess($employee);

        return view('employees.insurance.create', [
            'employee' => $employee,
            'insurance' => null
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $validated = $request->validate([
            'insurance_number' => 'required|string|max:30|unique:insurances,insurance_number',
            'insurance_type' => 'required|in:KES,TK,N-BPJS',
            'faskes_name' => 'required|string|max:255',
            'faskes_address' => 'required|string|max:500',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,NONAKTIF',
            'insurance_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        $user = Auth::user();

        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            $payload = $validated;
            $payload['employee_id'] = $employee->id;

            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $payload['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
            }

            $tempModel = new Insurance($payload);
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create');
            return redirect()->route('employees.insurance.index', $employee)->with('success', 'Permintaan penambahan data asuransi telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();
                $editRequest = $notifier->createEditRequest(
                    new Insurance(),
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'create'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    if (!empty($validated['insurance_file'])) {
                        Storage::disk('public')->delete($validated['insurance_file']);
                    }
                    return back()->with('error', 'Failed to create insurance data request.');
                }

                DB::commit();
                return redirect()->route('employees.insurance.index', $employee)
                    ->with('info', 'Insurance data addition request has been sent and is awaiting approval.');
            }

            $employee->insurance()->create($validated);
            DB::commit();

            return redirect()->route('employees.insurance.index', $employee)->with('success', 'Insurance data added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($validated['insurance_file'])) {
                Storage::disk('public')->delete($validated['insurance_file']);
            }
            return back()->with('error', 'Failed to save data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, Insurance $insurance)
    {
        $this->authorizeAccess($employee);

        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('employees.insurance.edit', [
            'employee' => $employee,
            'insurance' => $insurance
        ]);
    }

    public function update(Request $request, Employee $employee, Insurance $insurance)
    {
        $this->authorizeAccess($employee);

        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'insurance_number' => 'required|string|max:30|unique:insurances,insurance_number,' . $insurance->id,
            'insurance_type' => 'required|in:KES,TK,N-BPJS',
            'faskes_name' => 'required|string|max:255',
            'faskes_address' => 'required|string|max:500',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,NONAKTIF',
            'insurance_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        $user = Auth::user();
        
        //-- APPROVAL LOGIC START (PERBAIKAN KONSISTENSI) --//
        if ($user && $user->role === 'hc') {
            $payload = $validated;
            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $payload['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
            }
            
            // Gunakan 'clone' untuk menjaga data original
            $tempModel = clone $insurance;
            // Isi clone dengan data baru dari validasi
            $tempModel->fill($payload);
            
            // Panggil metode public `captureModelChange`
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update');
            
            return redirect()->route('employees.insurance.index', $employee)->with('success', 'Permintaan perubahan data asuransi telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            // Upload new file (if any)
            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '.' . $file->getClientOriginalExtension();
                $validated['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
            }

            // Approval flow for non-HC/non-superadmin
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $insurance,
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'update'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    if (!empty($validated['insurance_file'])) {
                        Storage::disk('public')->delete($validated['insurance_file']);
                    }
                    return back()->with('error', 'Failed to create insurance data update request.');
                }

                DB::commit();
                return redirect()->route('employees.insurance.index', $employee)
                    ->with('info', 'Insurance data update request has been sent and is awaiting approval.');
            }

            // If HC/Superadmin, directly update
            if (isset($validated['insurance_file']) && $insurance->insurance_file) {
                Storage::disk('public')->delete($insurance->insurance_file);
            }

            $insurance->update($validated);
            DB::commit();

            return redirect()->route('employees.insurance.index', $employee)
                ->with('success', 'Insurance data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($validated['insurance_file'])) {
                Storage::disk('public')->delete($validated['insurance_file']);
            }

            return back()->with('error', 'Failed to update data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, Insurance $insurance)
    {
        $this->authorizeAccess($employee);

        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        
        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            ApprovalWorkflowService::captureModelChange($user, $insurance, 'delete');
            return redirect()->route('employees.insurance.index', $employee)->with('success', 'Permintaan penghapusan data asuransi telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            // Approval flow for non-HC/non-superadmin
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $insurance,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create insurance data deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.insurance.index', $employee)
                    ->with('info', 'Insurance data deletion request has been sent and is awaiting approval.');
            }

            // If HC/Superadmin, directly delete
            if ($insurance->insurance_file) {
                Storage::disk('public')->delete($insurance->insurance_file);
            }
            $insurance->delete();

            DB::commit();
            return redirect()->route('employees.insurance.index', $employee)
                ->with('success', 'Insurance data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete data: ' . $e->getMessage());
        }
    }

    private function authorizeAccess(Employee $employee)
    {
        $user = Auth::user();

        if (in_array($user->role, ['hc', 'superadmin'])) {
            return true;
        }

        if ($user->employee && $user->employee->id === $employee->id) {
            return true;
        }

        abort(403, 'Unauthorized action.');
    }
}

