<?php

namespace App\Http\Controllers;

use App\Models\InterviewSchedule;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\InterviewScheduleNotification;

class InterviewScheduleController extends Controller
{
   private function canAccessSchedule($schedule)
{
    $user = Auth::user();

    if (in_array($user->role, ['superadmin', 'hc'])) {
        return true;
    }

    switch ($schedule->interview_type) {
            case 'User':
                // Manager divisi yang sama
                if ($user->role === 'manager' && $user->employee->division_id === $schedule->applicant->division_id) {
                    return true;
                }

                // Section head hanya jika tidak ada manager di divisinya
                if ($user->role === 'section_head' && $user->employee->division_id === $schedule->applicant->division_id) {
                    $hasManager = User::whereHas('employee', function ($q) use ($user) {
                        $q->where('division_id', $user->employee->division_id);
                    })->where('role', 'manager')->exists();

                    return !$hasManager;
                }
                return false;

            case 'Direksi':
                return $user->role === 'direksi';

            default:
                return false;
        }
    }

    public function index(Applicant $applicant)
{
    $user = Auth::user();
    $role = $user->role;

    // Query dasar
    $query = $applicant->interviewSchedules()->latest();

    // Filter data sesuai role
    if (in_array($role, ['hc', 'superadmin'])) {
        // HC & Superadmin bisa lihat semua
        $schedules = $query->paginate(10);

    } elseif ($role === 'direksi') {
        // Direksi hanya lihat interview type = Direksi
        $schedules = $query
            ->where('interview_type', 'Direksi')
            ->paginate(10);

    } elseif ($role === 'manager') {
        // Manager hanya lihat type User dan divisi pelamar sesuai divisinya
        $schedules = $query
            ->where('interview_type', 'User')
            ->whereHas('applicant', function ($q) use ($user) {
                $q->where('division_id', $user->employee->division_id);
            })
            ->paginate(10);

    } elseif ($role === 'section_head') {
        // Section Head hanya jika divisinya tidak memiliki Manager
        $hasManager = User::whereHas('employee', function ($q) use ($user) {
            $q->where('division_id', $user->employee->division_id);
        })->where('role', 'manager')->exists();

        if ($hasManager) {
            abort(403, 'Unauthorized action.');
} else {
    $schedules = $query
        ->where('interview_type', 'User')
        ->whereHas('applicant', function ($q) use ($user) {
            $q->where('division_id', $user->employee->division_id);
        })
        ->paginate(10);
}


    } else {
        abort(403, 'Unauthorized');
    }

    // Cek progress applicant
    $hasRejected = $applicant->recruitmentProgresses()
        ->where('offering_status', 'rejected')
        ->exists();

    $offeringAccepted = $applicant->recruitmentProgresses()
        ->where('stage', 'offering_letter')
        ->where('offering_status', 'accepted')
        ->exists();

    // Hanya HC/Superadmin yang bisa menambah interview
    $canAddInterview = in_array($role, ['superadmin', 'hc']) 
        && !$offeringAccepted 
        && !$hasRejected;

    return view('interview_schedule.index', compact('schedules', 'applicant', 'canAddInterview'));
}


    public function create(Applicant $applicant)
    {
        $this->authorizeAccessByRole('create');
        // Ambil opsi interviewer tergantung tipe interview
        $divisionId = $applicant->division_id;

       // Cek apakah ada manager di divisi ini
        $hasManager = User::whereHas('employee', function ($q) use ($divisionId) {
            $q->where('division_id', $divisionId);
        })->where('role', 'manager')->exists();

        // Jika ada manager, hanya tampilkan manager. Jika tidak, tampilkan section_head
        $userInterviewers = User::where('role', $hasManager ? 'manager' : 'section_head')
            ->whereHas('employee', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })
            ->get();

        $hcInterviewers = User::whereIn('role', ['hc', 'superadmin'])->get();
        $direksiInterviewers = User::where('role', 'direksi')->get();

        return view('interview_schedule.create', compact('applicant', 'userInterviewers', 'hcInterviewers', 'direksiInterviewers'));
    }

    public function store(Request $request, Applicant $applicant)
{
    $this->authorizeAccessByRole('create');

    $request->validate([
        'interview_type' => 'required|in:User,HC,Direksi',
        'interview_date' => 'required|date',
        'interviewer_id' => 'required|exists:users,id',
        'location' => 'required|string|max:255',
        'result' => 'nullable|string',
    ]);

    // Simpan data jadwal interview
     $schedule = $applicant->interviewSchedules()->create([
            'interview_type' => $request->interview_type,
            'interview_date' => $request->interview_date,
            'interviewer_id' => $request->interviewer_id,
            'location' => $request->location,
            'result' => $request->result,
        ]);

        // Kirim notifikasi hanya ke interviewer yang dipilih
        $interviewer = User::find($request->interviewer_id);
        if ($interviewer) {
            $interviewer->notify(new InterviewScheduleNotification($schedule));
        }
        
    return redirect()
        ->route('interview-schedule.index', $applicant->id)
        ->with('success', 'Interview schedule has been created and notification sent to the selected interviewer.');
}

   public function show(Applicant $applicant, InterviewSchedule $schedule)
    {
       // $user = Auth::user();

        if (!$this->canAccessSchedule($schedule)) {
            abort(403, 'Unauthorized');
        }

        return view('interview_schedule.show', compact('schedule', 'applicant'));
    }

   public function edit(Applicant $applicant, InterviewSchedule $schedule)
{
    if (!$this->canAccessSchedule($schedule)) {
        abort(403, 'Unauthorized');
    }

    $divisionId = $applicant->division_id;

        // Cek apakah ada manager di divisi ini
        $hasManager = User::whereHas('employee', function ($q) use ($divisionId) {
            $q->where('division_id', $divisionId);
        })->where('role', 'manager')->exists();

        // Jika ada manager, hanya tampilkan manager. Jika tidak, tampilkan section_head
        $userInterviewers = User::where('role', $hasManager ? 'manager' : 'section_head')
            ->whereHas('employee', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })
            ->get();

        $hcInterviewers = User::whereIn('role', ['hc', 'superadmin'])->get();
        $direksiInterviewers = User::where('role', 'direksi')->get();

        return view('interview_schedule.edit', compact('schedule', 'applicant', 'userInterviewers', 'hcInterviewers', 'direksiInterviewers'));
}

public function update(Request $request, Applicant $applicant, InterviewSchedule $schedule)
{
    if (!$this->canAccessSchedule($schedule)) {
        abort(403, 'Unauthorized');
    }

    // Validasi input form
    $request->validate([
        'interview_type' => 'required|in:User,HC,Direksi',
        'interview_date' => 'required|date',
        'interviewer_id' => 'required|exists:users,id',
        'location' => 'required|string|max:255',
        'result' => 'nullable|string',
    ]);

    // Simpan perubahan ke database
    $schedule->update([
        'interview_type' => $request->interview_type,
        'interview_date' => $request->interview_date,
        'interviewer_id' => $request->interviewer_id,
        'location' => $request->location,
        'result' => $request->result,
    ]);

    // Kirim notifikasi ke interviewer yang dipilih (seperti di method store)
    $interviewer = User::find($request->interviewer_id);
    if ($interviewer) {
        $interviewer->notify(new InterviewScheduleNotification($schedule));
    }
        return redirect()
            ->route('interview-schedule.index', $applicant->id)
            ->with('success', 'Interview schedule was updated and notification sent to the selected interviewer.');
}

public function destroy(Applicant $applicant, InterviewSchedule $schedule)
{
    if (!$this->canAccessSchedule($schedule)) {
        abort(403, 'Unauthorized');
    }

    $schedule->delete();

     return redirect()
            ->route('interview-schedule.index', $applicant->id)
            ->with('success', 'Interview schedule has been deleted.');
}

    private function authorizeAccessByRole($action)
{
    $user = Auth::user();

    if ($user->role === 'staff') {
        abort(403, 'Staff tidak memiliki akses untuk ' . $action);
    }
}
}
