<?php

namespace App\Http\Controllers;

use App\Models\CareerHistory;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Division;
use App\Models\EmployeeEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\RequestNotifierService;
use App\Notifications\EmployeeEditRequestNotification;

class CareerHistoryController extends Controller
{
    /**
     * Display a listing of the career histories.
     */
    public function index(Employee $employee)
    {
        $user = Auth::user();

        // Non-superadmin/hc/direksi can only access their own career history
        if (!in_array($user->role, ['superadmin', 'hc', 'direksi']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have permission to access this career history.');
        }

        $careerHistories = CareerHistory::where('employee_id', $employee->id)
            ->with(['position', 'division'])
            ->orderBy('id')
            ->get();

        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        return view('career-path.career_histories.index', compact('careerHistories', 'employee', 'divisions', 'positions'));
    }

    /**
     * Show the form for creating a new career history.
     */
    public function create(Employee $employee)
    {
        $user = Auth::user();

        // Non-superadmin/hc can only create for themselves
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have permission to add this career history.');
        }

        $positions = Position::orderBy('title')->get()->pluck('title', 'id');
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get()->pluck('name', 'id');
        return view('career-path.career_histories.create', compact('employee', 'positions', 'divisions'));
    }

    /**
     * Store a newly created career history in storage.
     */
    public function store(Request $request, Employee $employee)
    {
        $user = Auth::user();

        // Non-superadmin/hc can only create for themselves
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have permission to add this career history.');
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
            'position_id',
            'division_id',
            'employee_type',
            'start_date',
            'end_date',
            'type',
            'notes'
        ]);
        $data['employee_id'] = $employee->id;

        try {
            DB::beginTransaction();

            // If not superadmin/hc, create an approval request
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    new CareerHistory(),
                    $data,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'create'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create career history request.');
                }
                DB::commit();
                return redirect()->route('employees.showCareer', $employee)
                    ->with('info', 'Career history addition request has been sent and is awaiting approval.');
            }

            // Close any active CareerHistory
            $activeCareerHistory = CareerHistory::where('employee_id', $employee->id)
                ->whereNull('end_date')
                ->first();

            if ($activeCareerHistory) {
                $activeCareerHistory->update([
                    'end_date' => Carbon::today(),
                ]);
            }

            CareerHistory::create($data);

            // Update Employee data based on the latest CareerHistory
            $employee->update([
                'position_id' => $data['position_id'],
                'division_id' => $data['division_id'],
                'employee_type' => $data['employee_type'],
            ]);

            DB::commit();
            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Career history added successfully and employee data updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified career history.
     */
    public function edit(Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }

        $user = Auth::user();
        // Non-superadmin/hc can only edit their own
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have permission to edit this career history.');
        }

        $positions = Position::orderBy('title')->get()->pluck('title', 'id');
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get()->pluck('name', 'id');
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

        $user = Auth::user();
        // Non-superadmin/hc can only update their own
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have permission to update this career history.');
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
            'position_id',
            'division_id',
            'employee_type',
            'start_date',
            'end_date',
            'type',
            'notes'
        ]);

        try {
            DB::beginTransaction();

            // If not superadmin/hc, create an approval request
            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $careerHistory,
                    $data,
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'update'
                );

                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to update career history request.');
                }
                DB::commit();
                return redirect()->route('employees.showCareer', $employee)
                    ->with('info', 'Career history update request has been sent and is awaiting approval.');
            }

            // Update CareerHistory
            if (is_null($careerHistory->end_date)) {
                // CASE: Active Career History
                $careerHistory->update($data);

                if (is_null($data['end_date'])) {
                    // Still active, update Employee
                    $employee->update([
                        'position_id' => $data['position_id'],
                        'division_id' => $data['division_id'],
                        'employee_type' => $data['employee_type'],
                    ]);
                } else {
                    // If end_date is set, check if it's in the past
                    if (Carbon::now()->greaterThan(Carbon::parse($data['end_date']))) {
                        // Clear employee position data
                        $employee->update([
                            'position_id' => null,
                            'division_id' => null,
                            'employee_type' => null,
                        ]);
                    }
                }
            } else {
                // CASE: Ended Career History
                $newEndDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

                if (is_null($newEndDate)) {
                    return back()->with('error', 'End date for a completed career history cannot be null.')->withInput();
                }

                if ($newEndDate->greaterThan(Carbon::today())) {
                    return back()->with('error', 'End date for a completed career history cannot be in the future.')->withInput();
                }

                $careerHistory->update($data);
            }

            DB::commit();
            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Career history updated successfully and employee data updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified career history from storage.
     */
    public function destroy(Employee $employee, CareerHistory $careerHistory)
    {
        if ($careerHistory->employee_id !== $employee->id) {
            abort(404);
        }

        $user = Auth::user();
        // Non-superadmin/hc can only delete their own
        if (!in_array($user->role, ['superadmin', 'hc']) && $employee->user_id !== $user->id) {
            abort(403, 'You do not have permission to delete this career history.');
        }

        try {
            DB::beginTransaction();

            if (!in_array($user->role, ['superadmin', 'hc'])) {
                $notifier = new RequestNotifierService();

                $editRequest = $notifier->createEditRequest(
                    $careerHistory,
                    [],
                    EmployeeEditRequestNotification::class,
                    ['employee_id' => $employee->id],
                    'delete'
                );
                
                if (!$editRequest) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to create career history deletion request.');
                }
                DB::commit();
                return redirect()->route('employees.showCareer', $employee)
                                 ->with('info', 'Career history deletion request has been sent and is awaiting approval.');
            }

            // If deleting an active CareerHistory (end_date is null)
            if (is_null($careerHistory->end_date)) {
                // Clear employee position data
                $employee->update([
                    'position_id' => null,
                    'division_id' => null,
                    'employee_type' => null,
                ]);
            }

            $careerHistory->delete();

            DB::commit();
            return redirect()->route('employees.showCareer', $employee)
                ->with('success', 'Career history deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employees.showCareer', $employee)
                ->with('error', 'Failed to delete career history: ' . $e->getMessage());
        }
    }
}