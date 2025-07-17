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
    public function index(Employee $employee)
    {
        $careerProjections = CareerProjection::where('employee_id', $employee->id)
            ->with(['employee', 'projectedPosition', 'creator'])
            ->get();
        return view('career-path.career_projections.index', compact('careerProjections', 'employee'));
    }

    /**
     * Show the form for creating career projection.
     */
    public function form(Employee $employee)
    {
        $careerProjection = CareerProjection::where('employee_id', $employee->id)->first();
        $positions = Position::orderBy('title')->pluck('title', 'id');

        return view('career-path.career_projections.form', compact('employee', 'careerProjection', 'positions'));
    }

    /**
     * Store or Update a newly created career projection in storage.
     */
    public function storeOrUpdate(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'projected_position_id' => 'required|exists:positions,id',
            'timeline' => 'required|in:1 Tahun,3 Tahun,5 Tahun',
            'status' => 'required|in:Direncanakan,Disetujui,Tercapai,Dibatalkan',
            'readiness_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['projected_position_id', 'timeline', 'status', 'readiness_notes']);
        $data['employee_id'] = $employee->id;

        $existing = CareerProjection::where('employee_id', $employee->id)->first();

        if ($existing) {
            // Update
            $existing->update($data);
            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Career projection updated successfully.');
        } else {
            // Create
            $data['created_by'] = null; // atau Auth::id() jika login tersedia
            CareerProjection::create($data);

            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Career projection created successfully.');
        }
    }

    /**
     * Remove the Specified career projection from storage.
     */
    public function destroy(Employee $employee, CareerProjection $careerProjection)
    {
        if ($careerProjection->employee_id !== $employee->id) {
            abort(404);
        }

        $careerProjection->delete();
        return redirect()->route('employees.showCareer', $employee)
            ->with('success', 'Career projection deleted successfully.');
    }
}