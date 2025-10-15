<?php

namespace App\Http\Controllers;

use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use App\Models\Employee;
use App\Models\KpiTemplate;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class KpiAssessmentController extends Controller
{
    /**
     * Display the assessment dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        // Close expired assessments and recreate automatically
        $this->closeExpiredAssessments();

        // Assessments where the user is the primary supervisor
        $assessmentsAsSupervisor = KpiAssessment::where('primary_supervisor_id', $user->id)
            ->with('employee.position', 'period')
            ->latest()->get();

        $myAssessments = collect();
        $availableTemplates = collect();
        $activePeriods = collect();

        if ($employee) {
            // Retrieve existing assessments for the user
            $myAssessments = KpiAssessment::where('employee_id', $employee->id)
                ->with('supervisor', 'period')
                ->latest()->get();

            // Retrieve active templates relevant to the employee's position
            if ($employee->position_id) {
                $availableTemplates = KpiTemplate::where('position_id', $employee->position_id)
                    ->where('is_active', true)
                    ->get();
            }

            // Retrieve active periods for starting new assessments
            $activePeriods = KpiPeriod::where('status', 'Aktif')
                ->orderBy('start_date', 'desc')
                ->get();
        }

        return view('kpi.assessments.index', compact(
            'assessmentsAsSupervisor',
            'myAssessments',
            'availableTemplates',
            'activePeriods'
        ));
    }

    /**
     * Display the form to start a new assessment session by the supervisor.
     */
    public function create()
    {
        $periods = KpiPeriod::where('status', 'Aktif')->orderBy('start_date', 'desc')->get();

        // Logic to find subordinates based on position hierarchy
        $atasanPositionId = Auth::user()->employee->position_id ?? null;
        $subordinates = collect();
        if ($atasanPositionId) {
            $childPositionIds = Position::where('parent_id', $atasanPositionId)->pluck('id');
            $subordinates = Employee::whereIn('position_id', $childPositionIds)
                ->with('position')
                ->where('status', 'Aktif')
                ->get();
        }

        $templatesByPosition = KpiTemplate::where('is_active', true)
            ->get()
            ->groupBy('position_id');

        return view('kpi.assessments.create', compact('periods', 'subordinates', 'templatesByPosition'));
    }

    /**
     * Store (initiate) a new assessment session by the supervisor.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kpi_period_id' => 'required|exists:kpi_periods,id',
            'selected_employees' => 'required|array|min:1',
            'selected_employees.*' => 'required|exists:employees,id',
            'employees' => 'required|array',
            'employees.*' => 'required|exists:kpi_templates,id',
        ]);

        $selectedEmployees = $validatedData['selected_employees'];

        DB::beginTransaction();
        try {
            foreach ($selectedEmployees as $employeeId) {
                if (!isset($validatedData['employees'][$employeeId])) {
                    continue;
                }
                $templateId = $validatedData['employees'][$employeeId];
                $employee = Employee::findOrFail($employeeId);
                $template = KpiTemplate::with('templateItems.scoringRules')->findOrFail($templateId);

                if ($employee->position_id != $template->position_id) {
                    throw new \Exception("Template '{$template->template_name}' is not suitable for position {$employee->position->title}.");
                }
                $existing = KpiAssessment::where('employee_id', $employeeId)
                    ->where('kpi_period_id', $validatedData['kpi_period_id'])
                    ->exists();
                if ($existing) {
                    continue;
                }

                $assessment = KpiAssessment::create([
                    'employee_id' => $employee->id,
                    'kpi_period_id' => $validatedData['kpi_period_id'],
                    'primary_supervisor_id' => Auth::id(),
                    'status' => 'Penyesuaian Target',
                ]);

                foreach ($template->templateItems as $templateItem) {
                    $assessmentItem = $assessment->assessmentItems()->create([
                        'kpi_indicator_id' => $templateItem->kpi_indicator_id,
                        'weight' => $templateItem->weight,
                        'target' => $templateItem->default_target,
                    ]);

                    if ($templateItem->scoringRules) {
                        foreach ($templateItem->scoringRules as $rule) {
                            $assessmentItem->scoringRules()->create([
                                'operator' => $rule->operator,
                                'value1' => $rule->value1,
                                'value2' => $rule->value2,
                                'score' => $rule->score,
                            ]);
                        }
                    }
                }

                $assessment->participants()->create(['assessor_id' => Auth::id(), 'role' => 'direct_supervisor']);
            }
            DB::commit();

            \App\Services\KpiNotifier::notifyNewSession(
                KpiPeriod::find($validatedData['kpi_period_id']),
                Auth::user(),
                Employee::whereIn('id', $selectedEmployees)->get()->pluck('user')
            );
            return redirect()->route('kpi-assessments.index')->with('success', 'Assessment session created successfully. Please adjust targets, weights, and scoring rules.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create session: ' . $e->getMessage())->withInput();
        }
    }

    public function show(KpiAssessment $kpiAssessment)
    {
        $user = Auth::user();

        $this->closeExpiredAssessments();

        $hasPermission = false;

        if ($user->role === 'hc' || $user->role === 'superadmin') {
            $hasPermission = true;
        }

        if (!$hasPermission && $user->role === 'manager') {
            if ($user->employee && $kpiAssessment->employee && $user->employee->division_id === $kpiAssessment->employee->division_id) {
                $hasPermission = true;
            }
        }

        if (!$hasPermission) {
            $isParticipant = $kpiAssessment->participants()->where('assessor_id', $user->id)->exists();
            if ($kpiAssessment->employee->user_id === $user->id || $isParticipant) {
                $hasPermission = true;
            }
        }

        if (!$hasPermission) {
            if ($user->id === $kpiAssessment->primary_supervisor_id) {
                $hasPermission = true;
            }
        }

        if (!$hasPermission) {
            abort(403, 'You do not have permission to view this assessment detail.');
        }

        $kpiAssessment->load(
            'assessmentItems.indicator',
            'assessmentItems.scoringRules',
            'assessmentItems.scores.participant',
            'participants.assessor',
            'employee',
            'period'
        );

        return view('kpi.assessments.show', compact('kpiAssessment'));
    }

    /**
     * Store adjustments (targets, weights, scoring rules) or assessment scores.
     */
    public function update(Request $request, KpiAssessment $kpiAssessment)
    {
        $user = Auth::user();
        $action = $request->input('action', 'submit');

        DB::beginTransaction();
        try {
            // Target Adjustment Phase
            if ($kpiAssessment->status === 'Penyesuaian Target') {
                $validated = $request->validate([
                    'items' => 'required|array',
                    'items.*.target' => 'required|string|max:255',
                    'items.*.weight' => 'required|numeric|min:0|max:100',
                    'items.*.rules' => 'nullable|array',
                    'items.*.rules.*.operator' => ['required', Rule::in(['<', '<=', '=', '>=', '>', 'between'])],
                    'items.*.rules.*.value1' => 'required|numeric',
                    'items.*.rules.*.value2' => 'nullable|numeric|required_if:items.*.rules.*.operator,between',
                    'items.*.rules.*.score' => 'required|numeric|min:0',
                ]);

                $totalWeight = array_sum(array_column($validated['items'], 'weight'));
                if (round($totalWeight) != 100) {
                    DB::rollBack();
                    return back()->with('error', 'Total weight must be exactly 100%.')->withInput();
                }

                foreach ($validated['items'] as $itemId => $data) {
                    $assessmentItem = $kpiAssessment->assessmentItems()->findOrFail($itemId);
                    $rules = $data['rules'] ?? [];

                    $indicatorName = $assessmentItem->indicator?->indicator_name ?? 'Unknown Indicator';

                    if (count($rules) === 2) {
                        $values = collect($rules)->pluck('value1')->unique();
                        if (!$values->contains($data['target'])) {
                            DB::rollBack();
                            return back()->with('error', "Target and both scoring rule values must match for indicator {$indicatorName}.")->withInput();
                        }
                    } elseif (count($rules) >= 3) {
                        $maxScoreRule = collect($rules)->sortByDesc('score')->first();
                        if ($maxScoreRule && $data['target'] != $maxScoreRule['value1']) {
                            DB::rollBack();
                            return back()->with('error', "Target and scoring rule with score {$maxScoreRule['score']} must match for indicator {$indicatorName}.")->withInput();
                        }
                    }

                    foreach ($rules as $rule) {
                        if ($rule['operator'] === 'between') {
                            $val1 = (float) $rule['value1'];
                            $val2 = (float) $rule['value2'];
                            $allValues = collect($rules)->pluck('value1')->merge(collect($rules)->pluck('value2'))->filter();
                            if (!$allValues->contains($val1) || !$allValues->contains($val2)) {
                                DB::rollBack();
                                return back()->with('error', "The 'between' rule for indicator {$indicatorName} must align with the boundary values of other scoring rules.")->withInput();
                            }
                        }
                    }

                    $assessmentItem->update([
                        'target' => $data['target'],
                        'weight' => $data['weight']
                    ]);

                    $assessmentItem->scoringRules()->delete();
                    if (!empty($rules)) {
                        foreach ($rules as $ruleData) {
                            $assessmentItem->scoringRules()->create($ruleData);
                        }
                    }
                }

                $kpiAssessment->participants()->firstOrCreate(
                    ['assessor_id' => $kpiAssessment->employee->user_id],
                    ['role' => 'self']
                );
                $kpiAssessment->update(['status' => 'Penilaian Diri']);

                DB::commit();
                \App\Services\KpiNotifier::notifyTargetAdjusted(
                    [$kpiAssessment->employee->user]
                );
                return redirect()
                    ->route('kpi-assessments.show', $kpiAssessment->id)
                    ->with('success', 'Adjustments saved successfully. The employee can now complete their self-assessment.');
            }

            // Self or Supervisor Assessment Phase
            $participant = $kpiAssessment->participants()->where('assessor_id', $user->id)->firstOrFail();

            $validatedData = $request->validate([
                'items' => 'required|array',
                'items.*.achievement_input' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            foreach ($validatedData['items'] as $itemId => $itemData) {
                $assessmentItem = $kpiAssessment->assessmentItems()->with('scoringRules')->findOrFail($itemId);

                $calculatedScore = 0;
                if (is_numeric($itemData['achievement_input'])) {
                    $calculatedScore = $this->calculateScoreFromRules(
                        $itemData['achievement_input'],
                        $assessmentItem->scoringRules
                    );
                }

                $assessmentItem->scores()->updateOrCreate(
                    ['participant_id' => $participant->id],
                    [
                        'achievement_input' => $itemData['achievement_input'],
                        'score' => $calculatedScore
                    ]
                );
            }

            if ($action === 'submit') {
                $periodEnd = $kpiAssessment->period->end_date->endOfDay();
                $now = now();
                $hoursRemaining = $now->diffInHours($periodEnd, false);
                $daysRemaining = $hoursRemaining / 24;

                if ($daysRemaining > 5) {
                    DB::rollBack();
                    $daysRemainingInt = floor($daysRemaining);
                    return back()->with(
                        'error',
                        "Assessment can only be submitted when 5 days or fewer remain in the period. Currently, about {$daysRemainingInt} days remain."
                    )->withInput();
                }

                $participant->update(['status' => 'Selesai', 'notes' => $validatedData['notes'] ?? null]);
                $this->updateAssessmentStatus($kpiAssessment);
                $successMessage = 'Assessment submitted successfully.';

                if ($participant->role === 'self') {
                    
                    $atasan = $kpiAssessment->supervisor;
                    \App\Services\KpiNotifier::notifySelfSubmitted($atasan, $user, $kpiAssessment->period);
                } elseif ($participant->role === 'direct_supervisor') {
                    
                    $user = $kpiAssessment->employee->user;
                    $hrUsers = \App\Models\User::whereIn('role', ['hc', 'superadmin'])->get();
                    \App\Services\KpiNotifier::notifySupervisorSubmitted($user, $hrUsers, $kpiAssessment->period);
                }
            } else {
                $participant->update(['notes' => $validatedData['notes'] ?? null]);
                $successMessage = 'Assessment draft saved successfully.';
            }

            DB::commit();
            return redirect()->route('kpi-assessments.index')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the workflow status of the assessment.
     */
    protected function updateAssessmentStatus(KpiAssessment $assessment)
    {
        $assessment->refresh();

        $allParticipants = $assessment->participants;
        $completedParticipants = $allParticipants->where('status', 'Selesai');

        if ($completedParticipants->count() === $allParticipants->count()) {
            $this->finalizeAssessment($assessment);
        } else {
            $selfAssessment = $allParticipants->where('role', 'self')->first();
            if ($selfAssessment && $selfAssessment->status === 'Selesai') {
                $assessment->update(['status' => 'Penilaian Atasan Langsung']);
            }
        }
    }

    /**
     * Calculate the final score based on adjusted scoring rules.
     */
    protected function finalizeAssessment(KpiAssessment $assessment)
    {
        $totalScore = 0;
        $supervisorParticipant = $assessment->participants()->where('role', 'direct_supervisor')->first();

        if (!$supervisorParticipant) {
            $assessment->update(['status' => 'Error', 'final_score' => 0]);
            return;
        }

        foreach ($assessment->assessmentItems as $item) {
            $finalScoreRecord = $item->scores()->where('participant_id', $supervisorParticipant->id)->first();

            if ($finalScoreRecord && is_numeric($finalScoreRecord->achievement_input)) {
                $calculatedScore = $this->calculateScoreFromRules(
                    $finalScoreRecord->achievement_input,
                    $item->scoringRules
                );

                $itemScoreWeighted = ($calculatedScore) * ($item->weight / 100);

                $item->update([
                    'achievement' => $finalScoreRecord->achievement_input,
                    'final_item_score' => $itemScoreWeighted
                ]);
                $totalScore += $itemScoreWeighted;
            }
        }

        $assessment->update([
            'final_score' => $totalScore,
            'status' => 'Selesai'
        ]);
    }

    /**
     * Helper to calculate score based on achievement and rule set.
     */
    private function calculateScoreFromRules($achievement, $rules)
    {
        if ($rules->isEmpty()) {
            return 0;
        }
        $achievement = (float) $achievement;
        foreach ($rules->sortByDesc('score') as $rule) {
            $value1 = (float) $rule->value1;
            $value2 = isset($rule->value2) ? (float) $rule->value2 : null;

            switch ($rule->operator) {
                case '<':
                    if ($achievement < $value1) return $rule->score;
                    break;
                case '<=':
                    if ($achievement <= $value1) return $rule->score;
                    break;
                case '=':
                    if ($achievement == $value1) return $rule->score;
                    break;
                case '>=':
                    if ($achievement >= $value1) return $rule->score;
                    break;
                case '>':
                    if ($achievement > $value1) return $rule->score;
                    break;
                case 'between':
                    if ($achievement >= $value1 && $achievement <= $value2) return $rule->score;
                    break;
            }
        }
        return 0;
    }

    /**
     * Helper: close expired assessments and create new ones
     */
    private function closeExpiredAssessments()
    {
        $now = Carbon::now();

        $expiredAssessments = KpiAssessment::whereHas('period', function ($q) use ($now) {
            $q->where('end_date', '<', $now);
        })->whereNotIn('status', ['Kadaluarsa', 'Selesai'])->get();

        foreach ($expiredAssessments as $assessment) {
            DB::transaction(function () use ($assessment) {
                $assessment->update(['status' => 'Kadaluarsa']);

                $periodType = $this->detectPeriodType($assessment->period);
                $newPeriod = $this->getNextPeriod($assessment->period, $periodType);

                if ($newPeriod) {
                    $newAssessment = $assessment->replicate();
                    $newAssessment->status = 'Penyesuaian Target';
                    $newAssessment->final_score = null;
                    $newAssessment->kpi_period_id = $newPeriod->id;
                    $newAssessment->save();

                    foreach ($assessment->assessmentItems as $item) {
                        $newItem = $item->replicate();
                        $newItem->kpi_assessment_id = $newAssessment->id;
                        $newItem->save();

                        foreach ($item->scoringRules as $rule) {
                            $newRule = $rule->replicate();
                            $newRule->kpi_assessment_item_id = $newItem->id;
                            $newRule->save();
                        }
                    }

                    \App\Services\KpiNotifier::notifyExpired(
                        $assessment->period,
                        $newPeriod,
                        $assessment->supervisor,
                        [$assessment->employee->user]
                    );
                }
            });
        }
    }

    /**
     * Detect period type from unique label (Weekly, Monthly, etc.)
     */
    private function detectPeriodType(KpiPeriod $period): string
    {
        if (str_starts_with($period->period_name, 'Mingguan')) {
            return 'mingguan';
        } elseif (str_starts_with($period->period_name, 'Bulanan')) {
            return 'bulanan';
        } elseif (str_starts_with($period->period_name, 'Triwulan')) {
            return 'triwulan';
        } elseif (str_starts_with($period->period_name, 'Semester')) {
            return 'per_6_bulan';
        } elseif (str_starts_with($period->period_name, '4 Bulanan')) {
            return 'per_4_bulan';
        } else {
            return 'tahunan';
        }
    }

    /**
     * Create the next period from the old period
     */
    private function getNextPeriod(KpiPeriod $oldPeriod, string $type): ?KpiPeriod
    {
        $nextStart = null;
        $nextEnd = null;
        $label = null;

        switch ($type) {
            case 'mingguan':
                $nextStart = Carbon::parse($oldPeriod->end_date)->addDay()->startOfWeek(Carbon::MONDAY);
                $nextEnd = $nextStart->copy()->endOfWeek(Carbon::SUNDAY);
                $label = "Mingguan (" . $nextStart->format('d M') . " - " . $nextEnd->format('d M Y') . ")";
                break;

            case 'bulanan':
                $nextStart = Carbon::parse($oldPeriod->start_date)->addMonth()->startOfMonth();
                $nextEnd = $nextStart->copy()->endOfMonth();
                $label = "Bulanan " . $nextStart->format('F Y');
                break;

            case 'triwulan':
                $nextStart = Carbon::parse($oldPeriod->start_date)->addMonths(3)->startOfQuarter();
                $nextEnd = $nextStart->copy()->endOfQuarter();
                $label = "Triwulan " . ceil($nextStart->quarter) . " " . $nextStart->format('Y');
                break;

            case 'per_4_bulan':
                $nextStart = Carbon::parse($oldPeriod->start_date)->addMonths(4)->startOfMonth();
                $nextEnd = $nextStart->copy()->addMonths(3)->endOfMonth();
                $label = "4 Bulanan (" . $nextStart->format('M Y') . " - " . $nextEnd->format('M Y') . ")";
                break;

            case 'per_6_bulan':
                $nextStart = Carbon::parse($oldPeriod->start_date)->addMonths(6)->startOfMonth();
                $nextEnd = $nextStart->copy()->addMonths(5)->endOfMonth();
                $semester = ($nextStart->month <= 6) ? 1 : 2;
                $label = "Semester " . $semester . " " . $nextStart->format('Y');
                break;

            case 'tahunan':
                $nextStart = Carbon::parse($oldPeriod->start_date)->addYear()->startOfYear();
                $nextEnd = $nextStart->copy()->endOfYear();
                $label = "Tahunan " . $nextStart->format('Y');
                break;
        }

        if ($nextStart && $nextEnd && $label) {
            return KpiPeriod::firstOrCreate(
                [
                    'period_name' => $label,
                    'start_date' => $nextStart->format('Y-m-d'),
                    'end_date' => $nextEnd->format('Y-m-d'),
                ],
                [
                    'status' => 'Aktif'
                ]
            );
        }

        return null;
    }
}