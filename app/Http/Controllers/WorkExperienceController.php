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
use App\Services\ApprovalWorkflowService;

class WorkExperienceController extends Controller
{
    /**
     * Generate nama file aman + unik
     */
    private function storeExperienceFile($file)
    {
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug     = Str::slug($original);
        $ext      = $file->getClientOriginalExtension();

        // 🔥 Nama file dijamin unik
        $filename = uniqid() . '_' . $slug . '.' . $ext;

        return $file->storeAs('experience_files', $filename, 'public');
    }

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

            if ($request->hasFile('reference_letter_file')) {
                $payload['reference_letter_file'] = $this->storeExperienceFile($request->file('reference_letter_file'));
            }

            if ($request->hasFile('salary_slip_file')) {
                $payload['salary_slip_file'] = $this->storeExperienceFile($request->file('salary_slip_file'));
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
                $validated['reference_letter_file'] =
                    $this->storeExperienceFile($request->file('reference_letter_file'));
            }

            if ($request->hasFile('salary_slip_file')) {
                $validated['salary_slip_file'] =
                    $this->storeExperienceFile($request->file('salary_slip_file'));
            }

            // Employee non-admin → create request
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
                    Storage::disk('public')->delete($validated['reference_letter_file'] ?? null);
                    Storage::disk('public')->delete($validated['salary_slip_file'] ?? null);

                    return back()->with('error', 'Failed to create work experience data request.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Work experience addition request sent for approval.');
            }

            // SUPERADMIN → langsung simpan
            $employee->workExperience()->create($validated);

            DB::commit();
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Work experience added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            Storage::disk('public')->delete($validated['reference_letter_file'] ?? null);
            Storage::disk('public')->delete($validated['salary_slip_file'] ?? null);

            return back()->with('error', 'Failed to save data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403);
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
            abort(403);
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
                $payload['reference_letter_file'] = $this->storeExperienceFile($request->file('reference_letter_file'));
            }
            if ($request->hasFile('salary_slip_file')) {
                $payload['salary_slip_file'] = $this->storeExperienceFile($request->file('salary_slip_file'));
            }

            $tempModel = clone $workExperience;
            $tempModel->fill($payload);

            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update');

            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Permintaan perubahan pengalaman kerja telah dikirim untuk approval.');
        }

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN dan user non-admin
        DB::beginTransaction();
        try {
            // Upload new file (jangan hapus file lama dulu!)
            if ($request->hasFile('reference_letter_file')) {
                $validated['reference_letter_file'] =
                    $this->storeExperienceFile($request->file('reference_letter_file'));
            }

            if ($request->hasFile('salary_slip_file')) {
                $validated['salary_slip_file'] =
                    $this->storeExperienceFile($request->file('salary_slip_file'));
            }

            // Employee non-admin → buat request approval
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

                    Storage::disk('public')->delete($validated['reference_letter_file'] ?? null);
                    Storage::disk('public')->delete($validated['salary_slip_file'] ?? null);

                    return back()->with('error', 'Failed to send update request.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Work experience update request sent for approval.');
            }

            // SUPERADMIN → lakukan update dan hapus file lama SETELAH berhasil upload file baru
            $oldReference = $workExperience->reference_letter_file;
            $oldSalary    = $workExperience->salary_slip_file;

            $workExperience->update($validated);

            // Hapus file lama hanya jika ada file baru
            if ($request->hasFile('reference_letter_file') && $oldReference) {
                Storage::disk('public')->delete($oldReference);
            }
            if ($request->hasFile('salary_slip_file') && $oldSalary) {
                Storage::disk('public')->delete($oldSalary);
            }

            DB::commit();
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Work experience updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            Storage::disk('public')->delete($validated['reference_letter_file'] ?? null);
            Storage::disk('public')->delete($validated['salary_slip_file'] ?? null);

            return back()->with('error', 'Failed to update data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403);
        }

        $user = Auth::user();

        // HC → hanya kirim approval
        if ($user->role === 'hc') {
            ApprovalWorkflowService::captureModelChange($user, $workExperience, 'delete');

            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Permintaan penghapusan pengalaman kerja telah dikirim untuk approval.');
        }

        DB::beginTransaction();
        try {
            // Non-admin → request terlebih dahulu
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
                    return back()->with('error', 'Failed to send deletion request.');
                }

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                    ->with('info', 'Deletion request sent for approval.');
            }

            // SUPERADMIN → hapus file fisik
            Storage::disk('public')->delete($workExperience->reference_letter_file ?? null);
            Storage::disk('public')->delete($workExperience->salary_slip_file ?? null);

            $workExperience->delete();

            DB::commit();
            return redirect()->route('employees.work-experience.index', $employee)
                ->with('success', 'Work experience deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete: ' . $e->getMessage());
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

        abort(403);
    }
}