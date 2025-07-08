<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EducationHistory;
use Illuminate\Http\Request;

class EducationHistoryController extends Controller
{
    public function index(Employee $employee)
    {
        $educationHistories = $employee->educationHistory;
        return view('employees.educationhistory.index', compact('employee', 'educationHistories'));
    }

    public function create(Employee $employee)
    {
        return view('employees.educationhistory.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'education_level' => 'required|in:SD,SMP,SMA,D1,D2,D3,S1,S2,S3',
            'institution_name' => 'required|string|max:150',
            'institution_address' => 'required|string',
            'major' => 'required|string|max:100',
            'start_year' => 'required|digits:4|integer',
            'end_year' => 'required|digits:4|integer|gte:start_year',
            'gpa_or_score' => 'required|numeric|between:0,9999.99',
            'certificate_number' => 'nullable|string|max:50',
        ]);

        $employee->educationHistory()->create($validated);

        return redirect()->route('employees.educationhistory.index', $employee)->with('success', 'Employee Education was Added.');
    }

    public function edit(Employee $employee, EducationHistory $educationHistory)
    {
        return view('employees.educationhistory.edit', compact('employee', 'educationHistory'));
    }

    public function update(Request $request, Employee $employee, EducationHistory $educationHistory)
    {
        $validated = $request->validate([
            'education_level' => 'required|in:SD,SMP,SMA,D1,D2,D3,S1,S2,S3',
            'institution_name' => 'required|string|max:150',
            'institution_address' => 'required|string',
            'major' => 'required|string|max:100',
            'start_year' => 'required|digits:4|integer',
            'end_year' => 'required|digits:4|integer|gte:start_year',
            'gpa_or_score' => 'required|numeric|between:0,9999.99',
            'certificate_number' => 'nullable|string|max:50',
        ]);

        $educationHistory->update($validated);

        return redirect()->route('employees.educationhistory.index', $employee)->with('success', 'Employee Education was Updated.');
    }

    public function destroy(Employee $employee, EducationHistory $educationHistory)
    {
        $educationHistory->delete();
        return redirect()->route('employees.educationhistory.index', $employee)->with('success', 'Employee Education was Deleted.');
    }
}
