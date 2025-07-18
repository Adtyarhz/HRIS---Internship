<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class WorkExperienceController extends Controller
{
  public function index(Employee $employee)
  {
   $workExperiences = $employee->workExperience()->orderBy('id', 'asc')->get();
    return view('employees.data.work-experience.index', compact('employee', 'workExperiences'));
  }
  public function create(Employee $employee)
{
    return view('employees.data.work-experience.create', [
        'employee' => $employee,
        'workExperience' => null // ⛳ kirim ini agar _form tidak error
    ]);
}

public function store(Request $request, Employee $employee)
{
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

    $employee->workExperience()->create($validated);

    return redirect()->route('employees.work-experience.index', $employee)->with('success', 'Work Experience was Added.');
}

  public function edit(Employee $employee, WorkExperience $workExperience)
  {
    return view('employees.data.work-experience.edit', [
      'employee' => $employee,
      'workExperience' => $workExperience
  ]);  
  }

  public function update(Request $request, Employee $employee, WorkExperience $workExperience)
{
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
  
    $workExperience->update($validated);

    return redirect()->route('employees.work-experience.index', $employee)->with('success', 'Work Experience was Updated.');
}
public function destroy(Employee $employee, WorkExperience $workExperience)
{
    // Pastikan hanya workExperience milik employee yang bisa dihapus
    if ($workExperience->employee_id !== $employee->id) {
        abort(403, 'Unauthorized action.');
    }

    $workExperience->delete();

    return redirect()->route('employees.work-experience.index', $employee)
        ->with('success', 'Work Experience was Deleted.');
}

}