<?php

namespace App\Http\Controllers;

use App\Models\InterviewSchedule;
use App\Models\Applicant;
use Illuminate\Http\Request;

class InterviewScheduleController extends Controller
{
    public function index(Applicant $applicant)
    {
        $schedules = $applicant->interviewSchedules()->latest()->paginate(10);
        return view('interview_schedule.index', compact('schedules', 'applicant'));
    }

    public function create(Applicant $applicant)
    {
        return view('interview_schedule.create', compact('applicant'));
    }

    public function store(Request $request, Applicant $applicant)
    {
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
        return view('interview_schedule.show', compact('schedule', 'applicant'));
    }

    public function edit(Applicant $applicant, InterviewSchedule $schedule)
    {
        return view('interview_schedule.edit', compact('schedule', 'applicant'));
    }

    public function update(Request $request, Applicant $applicant, InterviewSchedule $schedule)
    {
        $request->validate([
            'interview_type' => 'required|in:User,HC,Direksi',
            'interview_date' => 'required|date',
            'interviewer' => 'required',
            'location' => 'required',
            'result' => 'nullable',
        ]);

        $schedule->update($request->all());

        return redirect()->route('interview-schedule.index', $applicant->id)->with('success', 'Interview schedule has updated.');
    }

    public function destroy(Applicant $applicant, InterviewSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('interview-schedule.index', $applicant->id)->with('success', 'Interview schedule has deleted.');
    }
}