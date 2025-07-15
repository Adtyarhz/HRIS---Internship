<?php

namespace App\Http\Controllers;

use App\Models\CareerHistory;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CareerHistoryController extends Controller
{
    /**
     * Display a listing of the career histories.
     */
    public function index()
    {
        $careerHistories = CareerHistory::with(['employee', 'position', 'division'])->get();
        return view('career_histories.index', compact('careerHistories'));
    }

    /**
     * Show the form for creating a new career history.
     */
    public function create()
    {
        $employees = Employee::all()->pluck('name', 'id');
        $positions = Position::all()->pluck('name', 'id');
        $divisions = Division::all()->pluck('name', 'id');
        return view('career_histories.create', compact('employees', 'positions', 'divisions'));
    }

    /**
     * Store a newly created career history in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'position_id' => 'required|exists:positions,id',
            'division_id' => 'required|exists:divisions,id',
            'employee_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:Promosi,Mutasi,Demosi,Awal Masuk',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        CareerHistory::create($request->all());
        return redirect()->route('career_histories.index')->with('success', 'Career history created successfully.');
    }

    /**
     * Show the form for editing the specified career history.
     */
    public function edit(CareerHistory $careerHistory)
    {
        $employees = Employee::all()->pluck('name', 'id');
        $positions = Position::all()->pluck('name', 'id');
        $divisions = Division::all()->pluck('name', 'id');
        return view('career_histories.edit', compact('careerHistory', 'employees', 'positions', 'divisions'));
    }

    /**
     * Update the specified career history in storage.
     */
    public function update(Request $request, CareerHistory $careerHistory)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'position_id' => 'required|exists:positions,id',
            'division_id' => 'required|exists:divisions,id',
            'employee_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:Promosi,Mutasi,Demosi,Awal Masuk',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $careerHistory->update($request->all());
        return redirect()->route('career_histories.index')->with('success', 'Career history updated successfully.');
    }

    /**
     * Remove the specified career history from storage.
     */
    public function destroy(CareerHistory $careerHistory)
    {
        $careerHistory->delete();
        return redirect()->route('career_histories.index')->with('success', 'Career history deleted successfully.');
    }
}