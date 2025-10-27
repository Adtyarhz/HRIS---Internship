<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\RecruitmentProgress;
use App\Models\User;
use App\Notifications\RecruitmentStageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecruitmentProgressController extends Controller
{
    private array $stages = [
        'general_knowledge_test',
        'computer_skills_test',
        'hc_interview',
        'user_assessment',
        'bod_interview',
        'offering_letter',
    ];

    public function show(Applicant $applicant)
    {
        $this->authorizeDivisionAccess($applicant);

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
        $this->authorizeDivisionAccess($applicant);

        if (!in_array($stage, $this->stages)) abort(404);

        $allProgress = RecruitmentProgress::where('applicant_id', $applicant->id)->get()->keyBy('stage');
        $currentProgress = $allProgress[$stage] ?? null;

        // Ambil contract_type dari general_knowledge_test agar bisa tampil di semua stage
        $generalTest = $allProgress['general_knowledge_test'] ?? null;
        $contractType = optional($generalTest)->contract_type;

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
        $this->authorizeDivisionAccess($applicant);

        if (!in_array($stage, $this->stages)) abort(404);

        if (!$this->canEditStage($stage, auth()->user()->role, $applicant)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses tahap ini.');
        }

        // Validasi bahwa tahap sebelumnya sudah accepted
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

        // Ambil contract_type dari general_knowledge_test agar bisa tampil di stage lain
        $generalTest = RecruitmentProgress::where('applicant_id', $applicant->id)
            ->where('stage', 'general_knowledge_test')
            ->first();

        $contractType = optional($generalTest)->contract_type;

        return view('recruitment_progress.edit', compact('applicant', 'progress', 'stage', 'contractType'));
    }

    public function stageUpdate(Request $request, Applicant $applicant)
    {
        $this->authorizeDivisionAccess($applicant);

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

        if (!$this->canEditStage($stage, auth()->user()->role, $applicant)) {
            abort(403, 'Anda tidak memiliki izin untuk memperbarui tahap ini.');
        }

        // Upload file result jika ada
        if ($request->hasFile('result_file')) {
            $file = $request->file('result_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['result_file'] = $file->storeAs('result_files', $filename, 'public');
        }

        // 🔹 Field khusus hanya untuk general_knowledge_test dan computer_skills_test
        if (!in_array($stage, ['general_knowledge_test', 'computer_skills_test'])) {
            $generalTest = RecruitmentProgress::where('applicant_id', $applicant->id)
                ->where('stage', 'general_knowledge_test')
                ->first();

            $validated['contract_type'] = optional($generalTest)->contract_type;

            // Hilangkan field khusus general test
            unset($validated['slik_recap'], $validated['test_result'], $validated['score']);
        }

        // 🔸 Untuk computer_skills_test, tidak ada field 'slik_recap'
        if ($stage === 'computer_skills_test') {
            unset($validated['slik_recap']);
        }

        RecruitmentProgress::updateOrCreate(
            ['applicant_id' => $applicant->id, 'stage' => $stage],
            array_merge($validated, ['applicant_id' => $applicant->id])
        );

        // 🔔 Kirim notifikasi ke stage berikutnya
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

    private function canEditStage(string $stage, string $role, ?Applicant $applicant = null): bool
    {
        $permissions = [
            'general_knowledge_test' => ['superadmin', 'hc'],
            'computer_skills_test' => ['superadmin', 'hc'],
            'hc_interview' => ['superadmin', 'hc'],
            'user_assessment' => ['superadmin', 'manager', 'section_head'],
            'bod_interview' => ['superadmin', 'direksi'],
            'offering_letter' => ['superadmin', 'hc'],
        ];

        if (!in_array($role, $permissions[$stage] ?? [])) {
            return false;
        }

        // 🔒 Hanya interviewer yang sesuai untuk user_assessment
        if ($stage === 'user_assessment' && in_array($role, ['manager', 'section_head']) && $applicant) {
            $interview = $applicant->interviewSchedules()
                ->where('interviewer_id', auth()->id())
                ->first();

            return (bool) $interview;
        }

        return true;
    }

    private function authorizeDivisionAccess(Applicant $applicant): void
    {
        $user = auth()->user();

        if (in_array($user->role, ['manager', 'section_head'])) {
            $userDivisionId = optional($user->employee)->division_id;
            if ($applicant->division_id !== $userDivisionId) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses pelamar dari divisi lain.');
            }
        }
    }

    private function getNextStage($stage)
    {
        $index = array_search($stage, $this->stages);
        return $this->stages[$index + 1] ?? null;
    }

    private function getStageRoles($stage)
    {
        $permissions = [
            'general_knowledge_test' => ['superadmin', 'hc'],
            'computer_skills_test' => ['superadmin', 'hc'],
            'hc_interview' => ['superadmin', 'hc'],
            'user_assessment' => ['superadmin', 'manager', 'section_head'],
            'bod_interview' => ['superadmin', 'direksi'],
            'offering_letter' => ['superadmin', 'hc'],
        ];

        return $permissions[$stage] ?? [];
    }
}
