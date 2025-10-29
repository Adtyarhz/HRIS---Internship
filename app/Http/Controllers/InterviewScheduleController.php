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

  public function index()
{
    $user = Auth::user();
    $role = $user->role;

    // Base query untuk ambil jadwal interview
    $query = InterviewSchedule::with(['applicant', 'interviewer'])
        ->orderBy('interview_date', 'asc');

    // 🔒 Filter akses:
    // Hanya superadmin & hc yang bisa lihat semua jadwal
    if (!in_array($role, ['superadmin', 'hc'])) {
        // Role lain (direksi, manager, section head) hanya lihat jadwal interview miliknya
        $query->whereHas('interviewer', function ($q) use ($user) {
            $q->where('id', $user->id);
        });
    }

    $schedules = $query->get();

    // Format untuk kalender
    $events = $schedules->map(function ($item) {
        return [
            'id' => $item->id,
            'title' => ($item->applicant?->full_name ?? '-') . ' (' . ($item->interview_type ?? '-') . ')',
            'start' => $item->interview_date,
            'interviewer' => $item->interviewer?->name ?? '-',
            'location' => $item->location,
            'meeting_link' => $item->meeting_link,
            'description' => $item->result,
            'type' => $item->interview_type,
        ];
    });

    // 🔜 Filter jadwal 7 hari ke depan juga pakai aturan akses yang sama
    $upcomingQuery = InterviewSchedule::with(['applicant', 'interviewer'])
        ->whereBetween('interview_date', [now(), now()->addDays(7)])
        ->orderBy('interview_date', 'asc');

    if (!in_array($role, ['superadmin', 'hc'])) {
        $upcomingQuery->whereHas('interviewer', function ($q) use ($user) {
            $q->where('id', $user->id);
        });
    }

    $upcomingSchedules = $upcomingQuery->get();

    // 🧩 Hanya superadmin & hc yang boleh menambah interview
    $canAddInterview = in_array($role, ['superadmin', 'hc']);

    return view('interview_schedule.index', compact('events', 'upcomingSchedules', 'canAddInterview'));
}


public function getInterviewersByApplicant(Request $request)
{
    $applicant = Applicant::find($request->applicant_id);

    if (!$applicant) {
        return response()->json(['error' => 'Applicant not found'], 404);
    }

    $divisionId = $applicant->division_id;

    // Cek apakah ada manager di divisi ini
    $hasManager = User::whereHas('employee', function ($q) use ($divisionId) {
        $q->where('division_id', $divisionId);
    })->where('role', 'manager')->exists();

    // Ambil interviewer untuk tipe "User"
    $userInterviewers = User::where('role', $hasManager ? 'manager' : 'section_head')
        ->whereHas('employee', function ($q) use ($divisionId) {
            $q->where('division_id', $divisionId);
        })
        ->get(['id', 'name']);

    return response()->json($userInterviewers);
}
    public function create()
{
    $this->authorizeAccessByRole('create');

    // Ambil semua applicant untuk dropdown
    $applicants = Applicant::all();
    $hcInterviewers = User::whereIn('role', ['hc', 'superadmin'])->get();
    $direksiInterviewers = User::where('role', 'direksi')->get();
    $userInterviewers = User::whereIn('role', ['manager', 'section_head'])->get();

    return view('interview_schedule.create', [
        'applicants' => $applicants,
        'hcInterviewers' => $hcInterviewers,
        'direksiInterviewers' => $direksiInterviewers,
        'userInterviewers' => $userInterviewers, 
    ]);
}

    public function store(Request $request)
{
    $this->authorizeAccessByRole('create');

    $validated = $request->validate([
        'applicant_id' => 'required|exists:applicants,id',
        'interview_type' => 'required|in:User,HC,Direksi',
        'interview_date' => 'required|date',
        'interviewer_id' => 'required|exists:users,id',
        'location' => 'required|in:online,onsite',
        'meeting_link' => 'nullable|url',
        'result' => 'nullable|string',
    ]);

    // Simpan langsung
    $schedule = InterviewSchedule::create($validated);

        // Kirim notifikasi hanya ke interviewer yang dipilih
        $interviewer = User::find($request->interviewer_id);
        if ($interviewer) {
            $interviewer->notify(new InterviewScheduleNotification($schedule));
        }
        
    return redirect()
        ->route('interview-schedule.index')
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

    // Semua applicant untuk dropdown
    $applicants = Applicant::all();

    // Semua interviewer sama seperti create
    $userInterviewers = User::whereIn('role', ['manager', 'section_head'])->get();
    $hcInterviewers = User::whereIn('role', ['hc', 'superadmin'])->get();
    $direksiInterviewers = User::where('role', 'direksi')->get();

    // Route dan method form
    $route = route('interview-schedule.update', $schedule->id);
    $method = 'PUT';

    return view('interview_schedule.edit', compact(
        'schedule',
        'applicant',
        'applicants',
        'userInterviewers',
        'hcInterviewers',
        'direksiInterviewers',
        'route',
        'method'
    ));
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
        'location' => 'required|in:online,onsite',
        'meeting_link' => 'nullable|url',
        'result' => 'nullable|string',
    ]);

    // Jika lokasi bukan online, hapus meeting_link
    $meetingLink = $request->location === 'online' ? $request->meeting_link : null;

    // Update ke database
    $schedule->update([
        'interview_type' => $request->interview_type,
        'interview_date' => $request->interview_date,
        'interviewer_id' => $request->interviewer_id,
        'location' => $request->location,
        'meeting_link' => $meetingLink,
        'result' => $request->result,
    ]);

    // Kirim notifikasi ke interviewer yang dipilih
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
