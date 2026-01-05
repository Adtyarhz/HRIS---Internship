<?php

namespace App\Http\Controllers;

use App\Models\OvertimeApplication;
use App\Models\OvertimeApplicationTask;
use App\Models\OvertimeApplicationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Notifications\OvertimeApplicationNotification;
use App\Models\User;

class OvertimeApplicationController extends Controller
{
    // List of overtime applications
public function index()
{
    $user = Auth::user();

    if ($user->role === 'staff_bisnis' || $user->role === 'staff_support') {
        // staff can only see applications intended for them
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->where('employee_id', $user->employee->id)
            ->latest()
            ->paginate(10);

    } elseif ($user->role === 'section_head') {
        // section head can see those they created + those created for them
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->where(function ($q) use ($user) {
                $q->where('requested_by', $user->id)
                  ->orWhere('employee_id', $user->employee->id);
            })
            ->latest()
            ->paginate(10);

    } elseif ($user->role === 'manager') {
        // manager only sees what they created
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->where('requested_by', $user->id)
            ->latest()
            ->paginate(10);

    } elseif (in_array($user->role, ['hc', 'superadmin'])) {
        // HC & superadmin can see all
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->latest()
            ->paginate(10);

    } else {
        abort(403, 'Unauthorized access.');
    }

    return view('overtime-application.index', compact('applications'));
}

    // Create form
public function create()
{
    $user = Auth::user();

    // default empty
    $employees = collect();

    if (in_array($user->role, ['hc', 'superadmin'])) {
        // HC & Superadmin can choose all employees
        $employees = Employee::all();

    } else {
        $employee   = $user->employee;
        $divisionId = $employee->division_id ?? null;

        // check if there is a manager in this division
        $hasManager = false;
        if ($divisionId) {
            $hasManager = Employee::where('division_id', $divisionId)
                ->whereHas('user', function ($q) {
                    $q->where('role', 'manager');
                })
                ->exists();
        }

        if ($user->role === 'manager' && $hasManager) {
            // Manager can choose themself + section_head + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', function ($q) {
                    $q->whereIn('role', ['manager', 'section_head', 'staff_bisnis', 'staff_support']);
                })
                ->get();

        } elseif ($user->role === 'section_head' && !$hasManager) {
            // If there is no manager, section head can choose themself + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', function ($q) {
                    $q->whereIn('role', ['section_head', 'staff_bisnis', 'staff_support']);
                })
                ->get();

        } elseif (in_array($user->role, ['staff_bisnis', 'staff_support'])) {
            // Staff cannot create
           abort(403, 'Unauthorized access.');
        } else {
            abort(403, 'Unauthorized access.');
        }
    }

    return view('overtime-application.create', compact('employees'));
}

    // Store overtime application
public function store(Request $request)
{
    $user = Auth::user();

    // default empty
    $employees = collect();

    if (in_array($user->role, ['hc', 'superadmin'])) {
        // HC & Superadmin can choose all employees
        $employees = Employee::pluck('id')->toArray();

    } else {
        $employee   = $user->employee;
        $divisionId = $employee->division_id ?? null;

        // check if there is a manager in this division
        $hasManager = false;
        if ($divisionId) {
            $hasManager = Employee::where('division_id', $divisionId)
                ->whereHas('user', fn($q) => $q->where('role', 'manager'))
                ->exists();
        }

        if ($user->role === 'manager' && $hasManager) {
            // Manager can choose themself + section_head + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', fn($q) =>
                    $q->whereIn('role', ['manager', 'section_head', 'staff_bisnis', 'staff_support'])
                )
                ->pluck('id')
                ->toArray();

        } elseif ($user->role === 'section_head' && !$hasManager) {
            // If there is no manager, section head can choose themself + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', fn($q) =>
                    $q->whereIn('role', ['section_head', 'staff_bisnis', 'staff_support'])
                )
                ->pluck('id')
                ->toArray();

        } elseif (in_array($user->role, ['staff_bisnis', 'staff_support'])) {
            return redirect()->route('overtime-applications.index')
                ->with('error', 'You do not have access to apply for overtime.');
        } else {
            abort(403, 'Unauthorized access.');
        }
    }

    // ✅ Validate form + ensure employee_id is one the user is allowed to choose
    $request->validate([
        'employee_id'    => ['required', 'exists:employees,id', function ($attr, $value, $fail) use ($employees) {
            if (!in_array($value, $employees)) {
                $fail('You are not entitled to request overtime for this employee..');
            }
        }],
        'start_datetime' => 'required|date',
        'end_datetime'   => 'required|date|after:start_datetime',
        'reason'         => 'required|string',
        'tasks'          => 'required|array',
        'tasks.*'        => 'nullable|string',
    ]);

    // ✅ Save the application
    $application = OvertimeApplication::create([
        'employee_id'    => $request->employee_id,
        'requested_by'   => Auth::id(),
        'start_datetime' => $request->start_datetime,
        'end_datetime'   => $request->end_datetime,
        'reason'         => $request->reason,
        'status'         => 'Pending',
    ]);
    $employeeName = $application->employee->full_name ?? 'Employee';
    // ✅ Save tasks if any
    if ($request->has('tasks')) {
        foreach ($request->tasks as $task) {
            if (!empty($task)) {
                OvertimeApplicationTask::create([
                    'overtime_application_id' => $application->id,
                    'task_description'        => $task,
                    'is_completed'            => false,
                ]);
            }
        }
    }

    // ✅ Add history
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => Auth::id(),
        'action_type' => 'Created',
        'description' => 'Overtime request made.',
        'created_at'  => now(),
    ]);
    $hcUsers = User::whereIn('role', ['hc', 'superadmin'])->get();
foreach ($hcUsers as $hc) {
    $hc->notify(new OvertimeApplicationNotification($application, "New overtime request ($employeeName) pending approval."));
}
$employeeUser = $application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($application, "You are submitted for overtime, status: Pending."));
}
    return redirect()->route('overtime-applications.index')
        ->with('success', 'Overtime request successfully created.');
}

   // Edit form
public function edit(OvertimeApplication $overtime_application)
{
    // 🚫 Prevent entry to edit page if already Approved/Rejected
    if (in_array($overtime_application->status, ['Approved', 'Rejected'])) {
        return redirect()
            ->route('overtime-applications.index')
            ->with('error', 'Applications that have been approved/rejected cannot be edited.');
    }
    $user = Auth::user();
    $employee = $user->employee ?? null;
    $divisionId = $employee->division_id ?? null;

    // check if there is a manager in the division
    $hasManager = false;
    if ($divisionId) {
        $hasManager = Employee::where('division_id', $divisionId)
            ->whereHas('user', function ($q) {
                $q->where('role', 'manager');
            })
            ->exists();
    }

    // Limit edit access
    if (
        ($user->role === 'manager' && $hasManager) ||
        ($user->role === 'section_head' && !$hasManager) ||
        in_array($user->role, ['hc', 'superadmin'])
    ) {
        $employees = Employee::all();
        $application = $overtime_application->load('tasks');

        return view('overtime-application.edit', compact('application', 'employees'));
    }

    abort(403, 'Unauthorized access.');
}

    // Update overtime application
    public function update(Request $request, OvertimeApplication $overtime_application)
    {
        // 🚫 Prevent update if already Approved/Rejected
    if (in_array($overtime_application->status, ['Approved', 'Rejected'])) {
        return redirect()
            ->route('overtime-applications.index')
            ->with('error', 'Applications that have been approved/rejected cannot be changed.');
    }
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'start_datetime' => 'required|date',
            'end_datetime'   => 'required|date|after:start_datetime',
            'reason'         => 'required|string',
            'tasks'          => 'required|array',
            'tasks.*'        => 'nullable|string',
        ]);

        $overtime_application->update([
            'employee_id'    => $request->employee_id,
            'start_datetime' => $request->start_datetime,
            'end_datetime'   => $request->end_datetime,
            'reason'         => $request->reason,
        ]);
        $employeeName = $overtime_application->employee->full_name ?? 'Employee';
        // ✅ Update tasks (delete old -> create new ones)
        $overtime_application->tasks()->delete();
        if ($request->has('tasks')) {
            foreach ($request->tasks as $task) {
                if (!empty($task)) {
                    OvertimeApplicationTask::create([
                        'overtime_application_id' => $overtime_application->id,
                        'task_description'        => $task,
                        'is_completed'            => false,
                    ]);
                }
            }
        }

        // ✅ Add history
        OvertimeApplicationHistory::create([
            'overtime_application_id' => $overtime_application->id,
            'action_by'   => Auth::id(),
            'action_type' => 'Updated',
            'description' => 'Overtime request updated.',
            'created_at'  => now(),
        ]);
        // after update & history is saved
$hcUsers = User::whereIn('role', ['hc', 'superadmin'])->get();
foreach ($hcUsers as $hc) {
    $hc->notify(new OvertimeApplicationNotification($overtime_application, "Overtime request ($employeeName) updated, awaiting approval."));
}

$employeeUser = $overtime_application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($overtime_application, "Your overtime request is updated, status: Pending."));
}

        return redirect()->route('overtime-applications.index')
                         ->with('success', 'Overtime request successfully updated.');
    }

    // Detail pengajuan lembur
    public function show($id)
    {
        $application = OvertimeApplication::with(['employee', 'requester', 'approver', 'tasks', 'histories.actor'])
            ->findOrFail($id);

        return view('overtime-application.show', compact('application'));
    }

    // Approve overtime
public function approve($id)
{
    $user = Auth::user();

    // ❌ Reject if not HC or Superadmin
    if (!in_array($user->role, ['hc', 'superadmin'])) {
        abort(403, 'Unauthorized access.');
    }

    $application = OvertimeApplication::findOrFail($id);
    $application->update([
        'status'      => 'Approved',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);
    $employeeName = $application->employee->full_name ?? 'Employee';
    // ✅ Add history
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => $user->id,
        'action_type' => 'Approved',
        'description' => 'Overtime request approved.',
        'created_at'  => now(),
    ]);
    // after status update & history is saved
$employeeUser = $application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($application, "Your overtime application {$application->status}."));
}

$requester = $application->requester;
if ($requester) {
    $requester->notify(new OvertimeApplicationNotification($application, "Overtime application ($employeeName) has {$application->status}."));
}

    return back()->with('success', 'Overtime request approved.');
}

// Reject overtime
public function reject($id, Request $request)
{
    $user = Auth::user();

    // ❌ Reject if not HC or Superadmin
    if (!in_array($user->role, ['hc', 'superadmin'])) {
        abort(403, 'Unauthorized access.');
    }

    // ✅ Validate reason is required
    $request->validate([
        'reason' => 'required|string|max:255',
    ]);

    $application = OvertimeApplication::findOrFail($id);
    $application->update([
        'status'      => 'Rejected',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);
    $employeeName = $application->employee->full_name ?? 'Employee';
    // ✅ Add history with reason
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => $user->id,
        'action_type' => 'Rejected',
        'description' => 'Overtime request rejected for the following reasons: '.$request->reason, // save reason
        'created_at'  => now(),
    ]);
    // after status update & history is saved
$employeeUser = $application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($application, "Your Overtime Application {$application->status}."));
}

$requester = $application->requester;
if ($requester) {
    $requester->notify(new OvertimeApplicationNotification($application, "Overtime application ($employeeName) has {$application->status}."));
}

    return redirect()->route('overtime-applications.show', $application->id)
        ->with('success', 'Overtime request rejected for the following reasons: '.$request->reason);
}

   public function toggleTask(Request $request, OvertimeApplicationTask $task)
{
    $application = $task->overtimeApplication;
    $user = Auth::user();

     // ✅ Ensure only the requester (supervisor) can change the task status
    if ($application->requested_by !== $user->id) {
        abort(403, 'Only the requester (supervisor) can update the task status.');
    }

    // 🚫 If the overtime is rejected -> cannot update task
    if ($application->status === 'Rejected') {
        return back()->with('error', 'The task cannot be updated because the overtime request has been rejected.');
    }

    // 🚫 If the overtime is still pending -> cannot update task
    if ($application->status === 'Pending') {
        return back()->with('error', 'The task cannot be updated because the overtime request is still awaiting approval.');
    }

    // ✅ Only if overtime is approved -> task can be updated
    if ($task->is_completed) {
        // if already finished -> revert to not finished
        $task->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    } else {
        // if not finished -> mark as finished
        $task->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }
    $employeeName = $application->employee->full_name ?? 'Employee';
    // Save history
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $task->overtime_application_id,
        'action_by'   => Auth::id(),
        'action_type' => 'Task Updated',
        'description' => "Task '{$task->task_description}' is marked as " 
            . ($task->is_completed ? "finished" : "not finished yet"),
        'created_at'  => now(),
    ]);
    // after task update & history is saved
$requester = $application->requester;
if ($requester) {
    $requester->notify(new OvertimeApplicationNotification($application, "($employeeName) overtime task status updated."));
}

// HC & superadmin
$hcUsers = User::whereIn('role', ['hc', 'superadmin'])->get();
foreach ($hcUsers as $hc) {
    $hc->notify(new OvertimeApplicationNotification($application, "($employeeName) overtime task status updated."));
}

    return back()->with('success', 'Task updated successfully.');
}


    // Delete overtime application
public function destroy($id)
{
    $application = OvertimeApplication::findOrFail($id);

    // ✅ Add history first before deleting
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => Auth::id(),
        'action_type' => 'Deleted',
        'description' => 'Overtime application deleted.',
        'created_at'  => now(),
    ]);

    // Then delete tasks & parent
    $application->tasks()->delete(); // delete all related tasks
    $application->delete();

    return redirect()
        ->route('overtime-applications.index')
        ->with('success', 'Overtime application removed.');
}

}
