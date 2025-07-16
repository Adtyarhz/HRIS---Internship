<?php

namespace App\Http\Controllers;

use App\Models\CareerHistory;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CareerHistoryController extends Controller
{
    /**
     * Display a listing of the career histories.
     */
    public function index(Employee $employee)
    {
        $careerHistories = CareerHistory::where('employee_id', $employee->id)
            ->with(['employee', 'position', 'division'])
            ->get();
        return view('career-path.career_histories.index', compact('careerHistories', 'employee'));
    }

    /**
     * Show the form for creating a new career history.
     */
    public function create(Employee $employee)
    {
        $positions = Position::orderBy('title')->get()->pluck('title', 'id');
        $divisions = Division::orderBy('name')->get()->pluck('name', 'id');
        return view('career-path.career_histories.create', compact('employee', 'positions', 'divisions'));
    }

    /**
     * Store a newly created career history in storage.
     */
    public function store(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'position_id' => 'required|exists:positions,id',
            'division_id' => 'required|exists:divisions,id',
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:Promosi,Mutasi,Demosi,Awal Masuk',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'position_id', 'division_id', 'employee_type',
            'start_date', 'end_date', 'type', 'notes'
        ]);
        $data['employee_id'] = $employee->id;

        CareerHistory::create($data);
        return redirect()->route('employees.showCareer', $employee)
            ->with('success', 'Career history created successfully.');
    }

    /**
     * Show the form for editing the specified career history.
     */
    public function edit(Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }

        $positions = Position::orderBy('title')->get()->pluck('title', 'id');
        $divisions = Division::orderBy('name')->get()->pluck('name', 'id');
        return view('career-path.career_histories.edit', compact('employee', 'careerHistory', 'positions', 'divisions'));
    }

    /**
     * Update the specified career history in storage.
     */
    public function update(Request $request, Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'position_id' => 'required|exists:positions,id',
            'division_id' => 'required|exists:divisions,id',
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:Promosi,Mutasi,Demosi,Awal Masuk',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'position_id', 'division_id', 'employee_type',
            'start_date', 'end_date', 'type', 'notes'
        ]);
        $data['employee_type'] = $employee->employee_type;

        $careerHistory->update($data);
        return redirect()->route('employees.showCareer', $employee)
            ->with('success', 'Career history updated successfully.');
    }

    /**
     * Remove the specified career history from storage.
     */
    public function destroy(Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }

        $careerHistory->delete();
        return redirect()->route('employees.showCareer', $employee)
            ->with('success', 'Career history deleted successfully.');
    }
}