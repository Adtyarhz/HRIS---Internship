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
    // Daftar pengajuan lembur
    // Daftar pengajuan lembur
public function index()
{
    $user = Auth::user();

    if ($user->role === 'staff_bisnis' || $user->role === 'staff_support') {
        // staff hanya bisa lihat pengajuan yg ditujukan untuk dirinya
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->where('employee_id', $user->employee->id)
            ->latest()
            ->paginate(10);

    } elseif ($user->role === 'section_head') {
        // section head bisa lihat yg dia buat sendiri + yg dibuat untuk dirinya
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->where(function ($q) use ($user) {
                $q->where('requested_by', $user->id)
                  ->orWhere('employee_id', $user->employee->id);
            })
            ->latest()
            ->paginate(10);

    } elseif ($user->role === 'manager') {
        // manager hanya lihat yg dia buat
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->where('requested_by', $user->id)
            ->latest()
            ->paginate(10);

    } elseif (in_array($user->role, ['hc', 'superadmin'])) {
        // HC & superadmin bisa lihat semua
        $applications = OvertimeApplication::with(['employee', 'requester', 'approver'])
            ->latest()
            ->paginate(10);

    } else {
        abort(403, 'Unauthorized access.');
    }

    return view('overtime-application.index', compact('applications'));
}

    // Form create
public function create()
{
    $user = Auth::user();

    // default kosong
    $employees = collect();

    if (in_array($user->role, ['hc', 'superadmin'])) {
        // HC & Superadmin bisa pilih semua employee
        $employees = Employee::all();

    } else {
        $employee   = $user->employee;
        $divisionId = $employee->division_id ?? null;

        // cek apakah ada manager di divisi ini
        $hasManager = false;
        if ($divisionId) {
            $hasManager = Employee::where('division_id', $divisionId)
                ->whereHas('user', function ($q) {
                    $q->where('role', 'manager');
                })
                ->exists();
        }

        if ($user->role === 'manager' && $hasManager) {
            // Manager bisa pilih dirinya + section_head + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', function ($q) {
                    $q->whereIn('role', ['manager', 'section_head', 'staff_bisnis', 'staff_support']);
                })
                ->get();

        } elseif ($user->role === 'section_head' && !$hasManager) {
            // Jika tidak ada manager, section head bisa pilih dirinya + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', function ($q) {
                    $q->whereIn('role', ['section_head', 'staff_bisnis', 'staff_support']);
                })
                ->get();

        } elseif (in_array($user->role, ['staff_bisnis', 'staff_support'])) {
            // Staff tidak boleh create
           abort(403, 'Unauthorized access.');
        } else {
            abort(403, 'Unauthorized access.');
        }
    }

    return view('overtime-application.create', compact('employees'));
}

    // Store pengajuan lembur
public function store(Request $request)
{
    $user = Auth::user();

    // default kosong
    $employees = collect();

    if (in_array($user->role, ['hc', 'superadmin'])) {
        // HC & Superadmin bisa pilih semua employee
        $employees = Employee::pluck('id')->toArray();

    } else {
        $employee   = $user->employee;
        $divisionId = $employee->division_id ?? null;

        // cek apakah ada manager di divisi ini
        $hasManager = false;
        if ($divisionId) {
            $hasManager = Employee::where('division_id', $divisionId)
                ->whereHas('user', fn($q) => $q->where('role', 'manager'))
                ->exists();
        }

        if ($user->role === 'manager' && $hasManager) {
            // Manager bisa pilih dirinya + section_head + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', fn($q) =>
                    $q->whereIn('role', ['manager', 'section_head', 'staff_bisnis', 'staff_support'])
                )
                ->pluck('id')
                ->toArray();

        } elseif ($user->role === 'section_head' && !$hasManager) {
            // Jika tidak ada manager, section head bisa pilih dirinya + staff
            $employees = Employee::where('division_id', $divisionId)
                ->whereHas('user', fn($q) =>
                    $q->whereIn('role', ['section_head', 'staff_bisnis', 'staff_support'])
                )
                ->pluck('id')
                ->toArray();

        } elseif (in_array($user->role, ['staff_bisnis', 'staff_support'])) {
            return redirect()->route('overtime-applications.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengajukan lembur.');
        } else {
            abort(403, 'Unauthorized access.');
        }
    }

    // ✅ Validasi form + pastikan employee_id termasuk yang boleh dipilih user
    $request->validate([
        'employee_id'    => ['required', 'exists:employees,id', function ($attr, $value, $fail) use ($employees) {
            if (!in_array($value, $employees)) {
                $fail('Anda tidak berhak mengajukan lembur untuk pegawai ini.');
            }
        }],
        'start_datetime' => 'required|date',
        'end_datetime'   => 'required|date|after:start_datetime',
        'reason'         => 'required|string',
        'tasks'          => 'required|array',
        'tasks.*'        => 'nullable|string',
    ]);

    // ✅ Simpan pengajuan
    $application = OvertimeApplication::create([
        'employee_id'    => $request->employee_id,
        'requested_by'   => Auth::id(),
        'start_datetime' => $request->start_datetime,
        'end_datetime'   => $request->end_datetime,
        'reason'         => $request->reason,
        'status'         => 'Pending',
    ]);

    // ✅ Simpan tasks jika ada
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

    // ✅ Tambah riwayat
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => Auth::id(),
        'action_type' => 'Created',
        'description' => 'Pengajuan lembur dibuat.',
        'created_at'  => now(),
    ]);
    $hcUsers = User::whereIn('role', ['hc', 'superadmin'])->get();
foreach ($hcUsers as $hc) {
    $hc->notify(new OvertimeApplicationNotification($application, "Pengajuan lembur baru menunggu persetujuan."));
}
$employeeUser = $application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($application, "Anda diajukan untuk lembur, status: Pending."));
}
    return redirect()->route('overtime-applications.index')
        ->with('success', 'Pengajuan lembur berhasil dibuat.');
}

   // Form edit
public function edit(OvertimeApplication $overtime_application)
{
    // 🚫 Cegah masuk ke halaman edit jika sudah Approved/Rejected
    if (in_array($overtime_application->status, ['Approved', 'Rejected'])) {
        return redirect()
            ->route('overtime-applications.index')
            ->with('error', 'Pengajuan yang sudah disetujui/ditolak tidak bisa diedit.');
    }
    $user = Auth::user();
    $employee = $user->employee ?? null;
    $divisionId = $employee->division_id ?? null;

    // cek apakah ada manager di divisi
    $hasManager = false;
    if ($divisionId) {
        $hasManager = Employee::where('division_id', $divisionId)
            ->whereHas('user', function ($q) {
                $q->where('role', 'manager');
            })
            ->exists();
    }

    // Batasi akses edit
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

    // Update pengajuan lembur
    public function update(Request $request, OvertimeApplication $overtime_application)
    {
        // 🚫 Cegah update jika sudah Approved/Rejected
    if (in_array($overtime_application->status, ['Approved', 'Rejected'])) {
        return redirect()
            ->route('overtime-applications.index')
            ->with('error', 'Pengajuan yang sudah disetujui/ditolak tidak bisa diubah.');
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

        // ✅ Update tasks (hapus lama → buat baru lagi)
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

        // ✅ Tambah riwayat
        OvertimeApplicationHistory::create([
            'overtime_application_id' => $overtime_application->id,
            'action_by'   => Auth::id(),
            'action_type' => 'Updated',
            'description' => 'Pengajuan lembur diperbarui.',
            'created_at'  => now(),
        ]);
        // setelah update & history disimpan
$hcUsers = User::whereIn('role', ['hc', 'superadmin'])->get();
foreach ($hcUsers as $hc) {
    $hc->notify(new OvertimeApplicationNotification($overtime_application, "Pengajuan lembur diperbarui, menunggu persetujuan."));
}

$employeeUser = $overtime_application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($overtime_application, "Pengajuan lembur Anda diperbarui, status: Pending."));
}

        return redirect()->route('overtime-applications.index')
                         ->with('success', 'Pengajuan lembur berhasil diperbarui.');
    }

    // Detail pengajuan lembur
    public function show($id)
    {
        $application = OvertimeApplication::with(['employee', 'requester', 'approver', 'tasks', 'histories.actor'])
            ->findOrFail($id);

        return view('overtime-application.show', compact('application'));
    }

    // Approve lembur
public function approve($id)
{
    $user = Auth::user();

    // ❌ Tolak jika bukan HC atau Superadmin
    if (!in_array($user->role, ['hc', 'superadmin'])) {
        abort(403, 'Unauthorized access.');
    }

    $application = OvertimeApplication::findOrFail($id);
    $application->update([
        'status'      => 'Approved',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);

    // ✅ Tambah riwayat
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => $user->id,
        'action_type' => 'Approved',
        'description' => 'Pengajuan lembur disetujui.',
        'created_at'  => now(),
    ]);
    // setelah update status & history disimpan
$employeeUser = $application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($application, "Pengajuan lembur Anda {$application->status}."));
}

$requester = $application->requester;
if ($requester) {
    $requester->notify(new OvertimeApplicationNotification($application, "Pengajuan lembur yang Anda buat {$application->status}."));
}

    return back()->with('success', 'Pengajuan lembur disetujui.');
}

// Reject lembur
public function reject($id, Request $request)
{
    $user = Auth::user();

    // ❌ Tolak jika bukan HC atau Superadmin
    if (!in_array($user->role, ['hc', 'superadmin'])) {
        abort(403, 'Unauthorized access.');
    }

    // ✅ Validasi alasan wajib diisi
    $request->validate([
        'reason' => 'required|string|max:255',
    ]);

    $application = OvertimeApplication::findOrFail($id);
    $application->update([
        'status'      => 'Rejected',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);

    // ✅ Tambah riwayat dengan alasan
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => $user->id,
        'action_type' => 'Rejected',
        'description' => 'Pengajuan lembur ditolak dengan alasan: '.$request->reason, // simpan alasan
        'created_at'  => now(),
    ]);
    // setelah update status & history disimpan
$employeeUser = $application->employee->user ?? null;
if ($employeeUser) {
    $employeeUser->notify(new OvertimeApplicationNotification($application, "Pengajuan lembur Anda {$application->status}."));
}

$requester = $application->requester;
if ($requester) {
    $requester->notify(new OvertimeApplicationNotification($application, "Pengajuan lembur yang Anda buat {$application->status}."));
}

    return redirect()->route('overtime-applications.show', $application->id)
        ->with('success', 'Pengajuan lembur ditolak dengan alasan: '.$request->reason);
}

   public function toggleTask(Request $request, OvertimeApplicationTask $task)
{
    $application = $task->overtimeApplication;

    // pastikan hanya karyawan pemilik task yg bisa update
    if ($application->employee->user_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    // 🚫 Jika lemburnya sudah ditolak → tidak boleh update task
    if ($application->status === 'Rejected') {
        return back()->with('error', 'Task tidak bisa diperbarui karena pengajuan lembur sudah ditolak.');
    }

    // 🚫 Jika lemburnya masih pending → tidak boleh update task
    if ($application->status === 'Pending') {
        return back()->with('error', 'Task tidak bisa diperbarui karena pengajuan lembur masih menunggu persetujuan.');
    }

    // ✅ Hanya jika lembur sudah disetujui → task boleh diupdate
    if ($task->is_completed) {
        // kalau sudah selesai → kembalikan ke belum selesai
        $task->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    } else {
        // kalau belum selesai → tandai selesai
        $task->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    // Simpan riwayat
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $task->overtime_application_id,
        'action_by'   => Auth::id(),
        'action_type' => 'Task Updated',
        'description' => "Task '{$task->task_description}' ditandai sebagai " 
            . ($task->is_completed ? "selesai" : "belum selesai"),
        'created_at'  => now(),
    ]);
    // setelah update task & history disimpan
$requester = $application->requester;
if ($requester) {
    $requester->notify(new OvertimeApplicationNotification($application, "Task pada lembur yang Anda buat diperbarui."));
}

// HC & superadmin
$hcUsers = User::whereIn('role', ['hc', 'superadmin'])->get();
foreach ($hcUsers as $hc) {
    $hc->notify(new OvertimeApplicationNotification($application, "Task lembur diperbarui oleh karyawan."));
}

    return back()->with('success', 'Task berhasil diperbarui.');
}


    // Hapus pengajuan lembur
public function destroy($id)
{
    $application = OvertimeApplication::findOrFail($id);

    // ✅ Tambah riwayat dulu sebelum hapus
    OvertimeApplicationHistory::create([
        'overtime_application_id' => $application->id,
        'action_by'   => Auth::id(),
        'action_type' => 'Deleted',
        'description' => 'Pengajuan lembur dihapus.',
        'created_at'  => now(),
    ]);

    // Baru hapus tasks & parent
    $application->tasks()->delete(); // hapus semua tasks terkait
    $application->delete();

    return back()->with('success', 'Pengajuan lembur dihapus.');
}

}
