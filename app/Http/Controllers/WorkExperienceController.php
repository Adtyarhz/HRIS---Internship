<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\WorkExperience;
use App\Models\EmployeeEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'company_phone' => 'required|string|max:20',
            'position_title' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'responsibilities' => 'required|string',
            'reason_to_leave' => 'required|string',
            'last_salary' => 'required|numeric',
            'reference_letter_file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg',
            'salary_slip_file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

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
        $user = auth()->user();
    if (!in_array($user->role, ['superadmin', 'hc'])) {
        EmployeeEditRequest::create([
            'employee_id'   => $employee->id,
            'method'        => 'create',
            'model'         => WorkExperience::class,
            'model_id'      => null, // belum ada id karena data belum dibuat
            'original_data' => [],   // data lama kosong
            'changed_data'  => $validated, // data yang diajukan
            'status'        => 'waiting',
            'requested_by'  => $user->id,
            'requested_at'  => now(),
        ]);
        return redirect()->route('employees.work-experience.index', $employee)
                         ->with('info', 'Permintaan penambahan data telah dikirim dan menunggu persetujuan.');
    }
        $employee->workExperience()->create($validated);

        return redirect()->route('employees.work-experience.index', $employee)->with('success', 'Work Experience was Added.');
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
            'company_phone' => 'required|string|max:20',
            'position_title' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'responsibilities' => 'required|string',
            'reason_to_leave' => 'required|string',
            'last_salary' => 'required|numeric',
            'reference_letter_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
            'salary_slip_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

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

        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                EmployeeEditRequest::create([
                    'employee_id'   => $employee->id,
                    'method'        => 'update',
                    'model'         => WorkExperience::class,
                    'model_id'      => $workExperience->id,
                    'original_data' => $workExperience->only(array_keys($validated)),
                    'changed_data'  => $validated,
                    'status'        => 'waiting',
                    'requested_by'  => $user->id,
                    'requested_at'  => now(),
                ]);

                DB::commit();
                return redirect()->route('employees.work-experience.index', $employee)
                                 ->with('info', 'Permintaan perubahan data telah dikirim dan menunggu persetujuan.');
            }

            $workExperience->update($validated);
            DB::commit();

            return redirect()->route('employees.work-experience.index', $employee)->with('success', 'Work Experience was Updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee, WorkExperience $workExperience)
    {
        $this->authorizeAccess($employee);

        if ($workExperience->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $workExperience->delete();

        return redirect()->route('employees.work-experience.index', $employee)
            ->with('success', 'Work Experience was Deleted.');
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
