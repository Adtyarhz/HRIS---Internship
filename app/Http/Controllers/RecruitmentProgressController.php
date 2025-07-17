<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\RecruitmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        // Jika ada yang rejected
        if ($rejectedStage !== null) {
            return redirect()->route('recruitment.stage.show', [$applicant->id, $rejectedStage]);
        }

        // Jika ada accepted terakhir dan ada stage setelahnya
        if ($lastAcceptedIndex !== -1 && isset($this->stages[$lastAcceptedIndex + 1])) {
            return redirect()->route('recruitment.stage.show', [$applicant->id, $this->stages[$lastAcceptedIndex + 1]]);
        }

        // Default ke stage pertama
        return redirect()->route('recruitment.stage.show', [$applicant->id, $this->stages[0]]);
    }

    public function stageShow(Applicant $applicant, string $stage)
    {
        if (!in_array($stage, $this->stages)) {
            abort(404);
        }

        $allProgress = RecruitmentProgress::where('applicant_id', $applicant->id)->get()->keyBy('stage');
        $currentProgress = $allProgress[$stage] ?? null;

        return view('recruitment_progress.stage', [
            'applicant' => $applicant,
            'progress' => $currentProgress,
            'stages' => $this->stages,
            'allProgress' => $allProgress,
            'stage' => $stage,
        ]);
    }

    public function stageEdit(Applicant $applicant, string $stage)
    {
        if (!in_array($stage, $this->stages)) {
            abort(404);
        }

        $currentIndex = array_search($stage, $this->stages);

        if ($currentIndex > 0) {
            $previousStage = $this->stages[$currentIndex - 1];
            $previousProgress = RecruitmentProgress::where('applicant_id', $applicant->id)
                ->where('stage', $previousStage)
                ->first();

            if (!$previousProgress || $previousProgress->offering_status !== 'accepted') {
                return redirect()->back()->with('error', 'You must complete the previous stage and mark it as accepted.');
            }
        }

        $progress = RecruitmentProgress::firstOrNew([
            'applicant_id' => $applicant->id,
            'stage' => $stage
        ]);

        return view('recruitment_progress.edit', compact('applicant', 'progress', 'stage'));
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

        if ($request->hasFile('result_file')) {
            $file = $request->file('result_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['result_file'] = $file->storeAs('result_files', $filename, 'public');
        }

        // Ambil contract type dari progress sebelumnya jika tidak ada input dan sebelumnya ada accepted
        if (empty($validated['contract_type'])) {
            $index = array_search($validated['stage'], $this->stages);
            for ($i = $index - 1; $i >= 0; $i--) {
                $prev = RecruitmentProgress::where('applicant_id', $applicant->id)
                    ->where('stage', $this->stages[$i])
                    ->where('offering_status', 'accepted')
                    ->first();
                if ($prev && $prev->contract_type) {
                    $validated['contract_type'] = $prev->contract_type;
                    break;
                }
            }
        }

        RecruitmentProgress::updateOrCreate(
            [
                'applicant_id' => $applicant->id,
                'stage' => $validated['stage']
            ],
            array_merge($validated, ['applicant_id' => $applicant->id])
        );

        return redirect()->route('recruitment.stage.show', [$applicant->id, $validated['stage']])
            ->with('success', 'Progress updated.');
    }
}
