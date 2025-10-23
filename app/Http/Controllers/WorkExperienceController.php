<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;
use App\Services\ApprovalWorkflowService; // <-- Ditambahkan

class WorkExperienceController extends Controller
{
    public function index(Employee $employee)
    {
        $this->authorizeAccess($employee);

        $workExperiences = $employee->workExperience()->orderBy('id', 'asc')->get();
        return view('employees.data.work-experience.index', compact('employee', 'workExperiences'));
    }

    public function create(Employee $employee)
    {
        $this->authorizeAccess($employee);

        return view('employees.data.work-experience.create', [
            'employee' => $employee,
            'workExperience' => null
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $this->authorizeAccess($employee);

        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:25|regex:/^[0-9+\-\s\(\)]+$/',
            'position_title' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'responsibilities' => 'required|string',
            'reason_to_leave' => 'required|string',
            'last_salary' => 'required|numeric',
            'reference_letter_file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg',
            'salary_slip_file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        $user = Auth::user();

        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            $payload = $validated;
            $payload['employee_id'] = $employee->id;
            
            if ($request->hasFile('reference_letter_file')) {
                $file = $request->file('reference_letter_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $payload['reference_letter_file'] = $file->storeAs('experience_files', $filename, 'public');
            }
            if ($request->hasFile('salary_slip_file')) {
                $file = $request->file('salary_slip_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $payload['salary_slip_file'] = $file->storeAs('experience_files', $filename, 'public');
            }

            $tempModel = new WorkExperience($payload);
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create');
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Permintaan penambahan pengalaman kerja telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//
        
        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            if ($request->hasFile('reference_letter_file')) {
                $file = $request->file('reference_letter_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['reference_letter_file'] = $file->storeAs('experience_files', $filename, 'public');
            }

            if ($request->hasFile('salary_slip_file')) {
                $file = $request->file('salary_slip_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['salary_slip_file'] = $file->storeAs('experience_files', $filename, 'public');
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    new WorkExperience(),
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'create'
                );
                if (!$editRequest) {
                    DB::rollBack();
                    if (!empty($validated['reference_letter_file'])) {
                        Storage::disk('public')->delete($validated['reference_letter_file']);
                    }
                    if (!empty($validated['salary_slip_file'])) {
                        Storage::disk('public')->delete($validated['salary_slip_file']);
                    }
                    return back()->with('error', 'Failed to create work experience data request.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Work experience addition request has been sent and is awaiting approval.');
            }

            $employee->workExperience()->create($validated);
            DB::commit();
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Work experience added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($validated['reference_letter_file'])) {
                Storage::disk('public')->delete($validated['reference_letter_file']);
            }
            if (!empty($validated['salary_slip_file'])) {
                Storage::disk('public')->delete($validated['salary_slip_file']);
            }

            return back()->with('error', 'Failed to save data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('employees.data.work-experience.edit', [
            'employee' => $employee,
            'workExperience' => $workExperience
        ]);
    }

    public function update(Request $request, Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:25|regex:/^[0-9+\-\s\(\)]+$/',
            'position_title' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'responsibilities' => 'required|string',
            'reason_to_leave' => 'required|string',
            'last_salary' => 'required|numeric',
            'reference_letter_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
            'salary_slip_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        $user = Auth::user();
        
        //-- APPROVAL LOGIC START (PERBAIKAN KONSISTENSI) --//
        if ($user && $user->role === 'hc') {
            $payload = $validated;
            if ($request->hasFile('reference_letter_file')) {
                $file = $request->file('reference_letter_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $payload['reference_letter_file'] = $file->storeAs('experience_files', $filename, 'public');
            }
            if ($request->hasFile('salary_slip_file')) {
                $file = $request->file('salary_slip_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $payload['salary_slip_file'] = $file->storeAs('experience_files', $filename, 'public');
            }
            
            // Gunakan 'clone' untuk menjaga data original
            $tempModel = clone $workExperience;
            // Isi clone dengan data baru
            $tempModel->fill($payload);
            
            // Panggil metode public `captureModelChange`
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update');
            
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Permintaan perubahan pengalaman kerja telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();

        try {
            if ($request->hasFile('reference_letter_file')) {
                $file = $request->file('reference_letter_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['reference_letter_file'] = $file->storeAs('experience_files', $filename, 'public');
                if ($workExperience->reference_letter_file) {
                    Storage::disk('public')->delete($workExperience->reference_letter_file);
                }
            }

            if ($request->hasFile('salary_slip_file')) {
                $file = $request->file('salary_slip_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validated['salary_slip_file'] = $file->storeAs('experience_files', $filename, 'public');
                if ($workExperience->salary_slip_file) {
                    Storage::disk('public')->delete($workExperience->salary_slip_file);
                }
            }

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $workExperience,
                    $validated,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id]
                );

                if (!$editRequest) {
                    DB::rollBack();
                    if (!empty($validated['reference_letter_file'])) {
                        Storage::disk('public')->delete($validated['reference_letter_file']);
                    }
                    if (!empty($validated['salary_slip_file'])) {
                        Storage::disk('public')->delete($validated['salary_slip_file']);
                    }
                    return back()->with('error', 'Failed to create work experience update request.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Work experience update request has been sent and is awaiting approval.');
            }

            $workExperience->update($validated);
            return redirect()->route('employees.work-experience.index', $employee)->with('success', 'Work experience updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($validated['reference_letter_file'])) {
                Storage::disk('public')->delete($validated['reference_letter_file']);
            }
            if (!empty($validated['salary_slip_file'])) {
                Storage::disk('public')->delete($validated['salary_slip_file']);
            }
            return back()->with('error', 'Failed to update data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        
        //-- APPROVAL LOGIC START --//
        if ($user && $user->role === 'hc') {
            ApprovalWorkflowService::captureModelChange($user, $workExperience, 'delete');
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Permintaan penghapusan pengalaman kerja telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $workExperience,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create work experience deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Work experience deletion request has been sent and is awaiting approval.');
            }

            if ($workExperience->reference_letter_file) {
                Storage::disk('public')->delete($workExperience->reference_letter_file);
            }
            if ($workExperience->salary_slip_file) {
                Storage::disk('public')->delete($workExperience->salary_slip_file);
            }

            $workExperience->delete();
            DB::commit();

            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Work experience deleted successfully.');
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

