<?php

namespace App\Http\Controllers;

use App\Models\CareerProjection;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CareerProjectionController extends Controller
{
    /**
     * Display a listing of the career projections.
     */
    public function index()
    {
        $careerProjections = CareerProjection::with(['employee', 'projectedPosition', 'creator'])->get();
        return view('career_projections.index', compact('careerProjections'));
    }

    /**
     * Show the form for creating a new career projection.
     */
    public function create()
    {
        $employees = Employee::all()->pluck('name', 'id');
        $positions = Position::all()->pluck('name', 'id');
        return view('career_projections.create', compact('employees', 'positions'));
    }

    /**
     * Store a newly created career projection in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'projected_position_id' => 'required|exists:positions,id',
            'timeline' => 'required|in:1 Tahun,3 Tahun,5 Tahun',
            'status' => 'required|in:Direncanakan,Disetujui,Tercapai,Dibatalkan',
            'readiness_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['created_by'] = Auth::id(); // Automatically set created_by to authenticated user
        CareerProjection::create($data);
        return redirect()->route('career_projections.index')->with('success', 'Career projection created successfully.');
    }

    /**
     * Show the form for editing the specified career projection.
     */
    public function edit(CareerProjection $careerProjection)
    {
        $employees = Employee::all()->pluck('name', 'id');
        $positions = Position::all()->pluck('name', 'id');
        return view('career_projections.edit', compact('careerProjection', 'employees', 'positions'));
    }

    /**
     * Update the specified career projection in storage.
     */
    public function update(Request $request, CareerProjection $careerProjection)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'projected_position_id' => 'required|exists:positions,id',
            'timeline' => 'required|in:1 Tahun,3 Tahun,5 Tahun',
            'status' => 'required|in:Direncanakan,Disetujui,Tercapai,Dibatalkan',
            'readiness_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['created_by'] = $careerProjection->created_by; // Preserve original created_by
        $careerProjection->update($data);
        return redirect()->route('career_projections.index')->with('success', 'Career projection updated successfully.');
    }

    /**
     * Remove the Specified career projection from storage.
     */
    public function destroy(CareerProjection $careerProjection)
    {
        $careerProjection->delete();
        return redirect()->route('career_projections.index')->with('success', 'Career projection deleted successfully.');
    }
}