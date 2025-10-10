<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\RecruitmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\RecruitmentStageNotification;

class RecruitmentProgressController extends Controller
{
    private array $stages = [
        'cv_screening',
        'general_knowledge_test',
        'user_assessment',
        'hc_interview',
        'bod_interview',
        'offering_letter',
    ];

    public function show(Applicant $applicant)
    {
        $applicant->load('recruitmentProgresses');
        $progresses = $applicant->recruitmentProgresses->keyBy('stage');

        $lastAcceptedIndex = -1;
        $rejectedStage = null;

        foreach ($this->stages as $index => $stage) {
            if (!isset($progresses[$stage])) continue;

            $status = $progresses[$stage]->offering_status;

            if ($status === 'in_progress') {
                return redirect()->route('recruitment.stage.show', [$applicant->id, $stage]);
            }

            if ($status === 'rejected' && $rejectedStage === null) {
                $rejectedStage = $stage;
            }

            if ($status === 'accepted') {
                $lastAcceptedIndex = $index;
            }
        }

        if ($rejectedStage !== null) {
            return redirect()->route('recruitment.stage.show', [$applicant->id, $rejectedStage]);
        }

        if ($lastAcceptedIndex !== -1 && isset($this->stages[$lastAcceptedIndex + 1])) {
            return redirect()->route('recruitment.stage.show', [$applicant->id, $this->stages[$lastAcceptedIndex + 1]]);
        }

        return redirect()->route('recruitment.stage.show', [$applicant->id, $this->stages[0]]);
    }

    public function stageShow(Applicant $applicant, string $stage)
    {
        if (!in_array($stage, $this->stages)) {
            abort(404);
        }

        $allProgress = RecruitmentProgress::where('applicant_id', $applicant->id)->get()->keyBy('stage');
        $currentProgress = $allProgress[$stage] ?? null;

        // Ambil contract_type dari CV Screening
        $cvProgress = $allProgress['cv_screening'] ?? null;
        $contractType = $cvProgress->contract_type ?? null;

        return view('recruitment_progress.stage', [
            'applicant' => $applicant,
            'progress' => $currentProgress,
            'stages' => $this->stages,
            'allProgress' => $allProgress,
            'stage' => $stage,
            'contractType' => $contractType,
        ]);
    }

   public function stageEdit(Applicant $applicant, string $stage)
{
    if (!in_array($stage, $this->stages)) {
        abort(404);
    }

    // Validasi role berdasarkan stage
    if (!$this->canEditStage($stage, auth()->user()->role)) {
        abort(403, 'Anda tidak memiliki izin untuk mengakses tahap ini.');
    }

    $currentIndex = array_search($stage, $this->stages);

    if ($currentIndex > 0) {
        $previousStage = $this->stages[$currentIndex - 1];
        $previousProgress = RecruitmentProgress::where('applicant_id', $applicant->id)
            ->where('stage', $previousStage)
            ->first();

        if (!$previousProgress || $previousProgress->offering_status !== 'accepted') {
            return redirect()->back()->with('error', 'You must complete the previous step and mark it as accepted.');
        }
    }

    $progress = RecruitmentProgress::firstOrNew([
        'applicant_id' => $applicant->id,
        'stage' => $stage
    ]);

    // Ambil contract_type dari CV Screening jika stage bukan cv_screening
    $cvProgress = RecruitmentProgress::where('applicant_id', $applicant->id)
        ->where('stage', 'cv_screening')
        ->first();

    $contractType = $cvProgress->contract_type ?? null;

    return view('recruitment_progress.edit', compact('applicant', 'progress', 'stage', 'contractType'));
}

    public function stageUpdate(Request $request, Applicant $applicant)
{
    $validated = $request->validate([
        'stage' => 'required|string|in:' . implode(',', $this->stages),
        'offering_status' => 'required|string|in:accepted,rejected,in_progress',
        'status_date' => 'required|date',
        'notes' => 'nullable|string',
        'rejected_reason' => 'nullable|required_if:offering_status,rejected|string',
        'contract_type' => 'nullable|string',
        'test_result' => 'nullable|string',
        'score' => 'nullable|string',
        'slik_recap' => 'nullable|string',
        'result_file' => 'nullable|file|mimes:pdf,doc,docx',
    ]);

    $stage = $validated['stage'];

    // Validasi role
    if (!$this->canEditStage($stage, auth()->user()->role)) {
        abort(403, 'Anda tidak memiliki izin untuk memperbarui tahap ini.');
    }

    // File upload
    if ($request->hasFile('result_file')) {
        $file = $request->file('result_file');
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $validated['result_file'] = $file->storeAs('result_files', $filename, 'public');
    }

    if ($stage !== 'cv_screening') {
        $cvScreening = RecruitmentProgress::where('applicant_id', $applicant->id)
            ->where('stage', 'cv_screening')
            ->first();
        $validated['contract_type'] = $cvScreening->contract_type ?? null;
    }

    if ($stage !== 'hc_interview') {
        unset($validated['slik_recap']);
    }

    RecruitmentProgress::updateOrCreate(
        ['applicant_id' => $applicant->id, 'stage' => $stage],
        array_merge($validated, ['applicant_id' => $applicant->id])
    );
    // Kirim notifikasi ke role berikutnya
    $nextStage = $this->getNextStage($stage);
    if ($nextStage) {
        $receivers = User::whereIn('role', $this->getStageRoles($nextStage))->get();
        foreach ($receivers as $user) {
            $user->notify(new RecruitmentStageNotification($applicant, $stage, $validated['offering_status']));
        }
    }

    return redirect()->route('recruitment.stage.show', [$applicant->id, $stage])
        ->with('success', 'Progress updated successfully.');
}
private function canEditStage(string $stage, string $role): bool
{
    $permissions = [
        'cv_screening' => ['superadmin', 'hc'],
        'general_knowledge_test' => ['superadmin', 'hc'],
        'user_assessment' => ['superadmin', 'manager', 'section_head'],
        'hc_interview' => ['superadmin', 'hc'],
        'bod_interview' => ['superadmin', 'direksi', 'hc'],
        'offering_letter' => ['superadmin','hc'],
    ];

    return in_array($role, $permissions[$stage] ?? []);
}
private function getNextStage($stage)
{
    $index = array_search($stage, $this->stages);
    return $this->stages[$index + 1] ?? null;
}

private function getStageRoles($stage)
{
    $permissions = [
        'cv_screening' => ['superadmin', 'hc'],
        'general_knowledge_test' => ['superadmin', 'hc'],
        'user_assessment' => ['superadmin', 'manager', 'section_head'],
        'hc_interview' => ['superadmin', 'hc'],
        'bod_interview' => ['superadmin', 'direksi', 'hc'],
        'offering_letter' => ['superadmin','hc'],
    ];

    return $permissions[$stage] ?? [];
}


}