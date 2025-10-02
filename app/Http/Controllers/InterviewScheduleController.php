<?php

namespace App\Http\Controllers;

use App\Models\InterviewSchedule;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            return $user->role === 'section_head' && $user->division_id === $schedule->applicant->division_id;
        case 'Direksi':
            return in_array($user->role, ['manager', 'direktur']);
        default:
            return false;
    }
}

    public function index(Applicant $applicant)
{
    $schedules = $applicant->interviewSchedules()->latest()->paginate(10);
    $role = auth()->user()->role;

    // cek progress applicant
    $hasRejected = $applicant->recruitmentProgresses()->where('offering_status', 'rejected')->exists();
    $offeringAccepted = $applicant->recruitmentProgresses()
        ->where('stage', 'offering_letter')
        ->where('offering_status', 'accepted')
        ->exists();

    // hanya superadmin / hc boleh add, dan syarat belum accepted/rejected
    $canAddInterview = in_array($role, ['superadmin', 'hc']) 
        && !$offeringAccepted 
        && !$hasRejected;

    return view('interview_schedule.index', compact('schedules', 'applicant', 'canAddInterview'));
}

    public function create(Applicant $applicant)
    {
        $this->authorizeAccessByRole('create');
        return view('interview_schedule.create', compact('applicant'));
    }

    public function store(Request $request, Applicant $applicant)
    {
        $this->authorizeAccessByRole('create');

        $request->validate([
            'interview_type' => 'required|in:User,HC,Direksi',
            'interview_date' => 'required|date',
            'interviewer' => 'required',
            'location' => 'required',
            'result' => 'nullable',
        ]);

        $applicant->interviewSchedules()->create($request->all());

        return redirect()->route('interview-schedule.index', $applicant->id)->with('success', 'Interview schedule has created.');
    }

   public function show(Applicant $applicant, InterviewSchedule $schedule)
{
    $user = Auth::user();

    if ($user->role === 'staff') {
        abort(403, 'Unauthorized');
    }

    return view('interview_schedule.show', compact('schedule', 'applicant'));
}

   public function edit(Applicant $applicant, InterviewSchedule $schedule)
{
    if (!$this->canAccessSchedule($schedule)) {
        abort(403, 'Unauthorized');
    }

    return view('interview_schedule.edit', compact('schedule', 'applicant'));
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
        'interviewer' => 'required|string|max:255',
        'location' => 'required|string|max:255',
        'result' => 'nullable|string',
    ]);

    // Simpan perubahan ke database
    $schedule->update([
        'interview_type' => $request->interview_type,
        'interview_date' => $request->interview_date,
        'interviewer' => $request->interviewer,
        'location' => $request->location,
        'result' => $request->result,
    ]);

    // Redirect ke halaman detail
    return redirect()
        ->route('interview-schedule.index', [$applicant->id, $schedule->id])
        ->with('success', 'Interview schedule was updated.');
}

public function destroy(Applicant $applicant, InterviewSchedule $schedule)
{
    if (!$this->canAccessSchedule($schedule)) {
        abort(403, 'Unauthorized');
    }

    $schedule->delete();

    return redirect()->route('interview-schedule.index', $applicant->id)->with('success', 'Interview schedule has deleted.');
}

    private function authorizeAccessByRole($action)
{
    $user = Auth::user();

    if ($user->role === 'staff') {
        abort(403, 'Staff tidak memiliki akses untuk ' . $action);
    }
}
}
