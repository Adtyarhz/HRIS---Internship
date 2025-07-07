<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InsuranceController extends Controller
{
    public function index(Employee $employee)
    {
        $insurances = $employee->insurance()->orderBy('id', 'asc')->get();
return view('employees.insurance.index', compact('employee', 'insurances'));
    }

    public function create(Employee $employee)
    {
        return view('employees.insurance.create', [
            'employee' => $employee,
            'insurance' => null
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'insurance_number' => 'required|string|max:30|unique:insurances,insurance_number',
            'insurance_type' => 'required|in:KES,TK,N-BPJS',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,NONAKTIF',
            'insurance_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        if ($request->hasFile('insurance_file')) {
            $file = $request->file('insurance_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
        }

        $employee->insurance()->create($validated);

        return redirect()->route('employees.insurance.index', $employee)->with('success', 'Insurance data was added.');
    }

    public function edit(Employee $employee, Insurance $insurance)
    {
        return view('employees.insurance.edit', [
            'employee' => $employee,
            'insurance' => $insurance
        ]);
    }

    public function update(Request $request, Employee $employee, Insurance $insurance)
    {
        $validated = $request->validate([
            'insurance_number' => 'required|string|max:30|unique:insurances,insurance_number,' . $insurance->id,
            'insurance_type' => 'required|in:KES,TK,N-BPJS',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,NONAKTIF',
            'insurance_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg',
        ]);

        if ($request->hasFile('insurance_file')) {
            $file = $request->file('insurance_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['insurance_file'] = $file->storeAs('insurance_files', $filename, 'public');
        }

        $insurance->update($validated);

        return redirect()->route('employees.insurance.index', $employee)->with('success', 'Insurance data was updated.');
    }

    public function destroy(Employee $employee, Insurance $insurance)
    {
        if ($insurance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $insurance->delete();

        return redirect()->route('employees.insurance.index', $employee)->with('success', 'Insurance data was deleted.');
    }
}
