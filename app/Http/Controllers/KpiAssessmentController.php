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
     * Menampilkan dashboard penilaian.
     */
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        // 🔄 Tutup assessment kadaluarsa + buat ulang otomatis
        $this->closeExpiredAssessments();

        // Penilaian di mana pengguna adalah atasan utama
        $assessmentsAsSupervisor = KpiAssessment::where('primary_supervisor_id', $user->id)
            ->with('employee.position', 'period')
            ->latest()->get();

        $myAssessments = collect();
        $availableTemplates = collect();
        $activePeriods = collect();

        if ($employee) {
            // Ambil penilaian yang sudah ada untuk diri sendiri
            $myAssessments = KpiAssessment::where('employee_id', $employee->id)
                ->with('supervisor', 'period')
                ->latest()->get();

            // Ambil template yang aktif dan relevan dengan jabatan karyawan
            if ($employee->position_id) {
                $availableTemplates = KpiTemplate::where('position_id', $employee->position_id)
                    ->where('is_active', true)
                    ->get();
            }

            // Ambil periode yang aktif untuk memulai penilaian baru
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
     * Menampilkan form untuk memulai sesi penilaian baru oleh atasan.
     */
    public function create()
    {
        $periods = KpiPeriod::where('status', 'Aktif')->orderBy('start_date', 'desc')->get();

        // Logika untuk mencari bawahan berdasarkan hierarki jabatan
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
     * Menyimpan (menginisiasi) sesi penilaian baru oleh atasan.
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
                // MODIFIKASI: Eager load relasi templateItems beserta scoringRules-nya
                $template = KpiTemplate::with('templateItems.scoringRules')->findOrFail($templateId);

                if ($employee->position_id != $template->position_id) {
                    throw new \Exception("Template '{$template->template_name}' tidak sesuai untuk jabatan {$employee->position->title}.");
                }
                $existing = KpiAssessment::where('employee_id', $employeeId)
                    ->where('kpi_period_id', $validatedData['kpi_period_id'])
                    ->exists();
                if ($existing)
                    continue;

                $assessment = KpiAssessment::create([
                    'employee_id' => $employee->id,
                    'kpi_period_id' => $validatedData['kpi_period_id'],
                    'primary_supervisor_id' => Auth::id(),
                    'status' => 'Penyesuaian Target',
                ]);

                foreach ($template->templateItems as $templateItem) {
                    // Buat assessment item dari template item
                    $assessmentItem = $assessment->assessmentItems()->create([
                        'kpi_indicator_id' => $templateItem->kpi_indicator_id,
                        'weight' => $templateItem->weight,
                        'target' => $templateItem->default_target,
                    ]);

                    // MODIFIKASI: Salin (copy) setiap scoring rule dari template ke assessment item
                    // PENJELASAN: Ini adalah langkah krusial. Kita membuat duplikat aturan skor
                    // sehingga penyesuaian di assessment ini tidak akan mengubah master template.
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

                // Tambahkan peserta atasan dulu untuk penyesuaian
                $assessment->participants()->create(['assessor_id' => Auth::id(), 'role' => 'direct_supervisor']);
            }
            DB::commit();
            return redirect()->route('kpi-assessments.index')->with('success', 'Sesi penilaian berhasil dibuat. Silakan sesuaikan target, bobot, dan aturan skor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat sesi: ' . $e->getMessage())->withInput();
        }
    }

    public function show(KpiAssessment $kpiAssessment)
    {
        $user = Auth::user();

        // 🔄 Tutup assessment kadaluarsa + buat ulang otomatis
        $this->closeExpiredAssessments();

        $hasPermission = false;

        // 1. HC / Admin
        if ($user->role === 'hc' || $user->role === 'superadmin') {
            $hasPermission = true;
        }

        // 2. Manager divisi yang sama
        if (!$hasPermission && $user->role === 'manager') {
            if ($user->employee && $kpiAssessment->employee && $user->employee->division_id === $kpiAssessment->employee->division_id) {
                $hasPermission = true;
            }
        }

        // 3. Peserta langsung (penilai / dinilai)
        if (!$hasPermission) {
            $isParticipant = $kpiAssessment->participants()->where('assessor_id', $user->id)->exists();
            if ($kpiAssessment->employee->user_id === $user->id || $isParticipant) {
                $hasPermission = true;
            }
        }

        // 4. Atasan utama
        if (!$hasPermission) {
            if ($user->id === $kpiAssessment->primary_supervisor_id) {
                $hasPermission = true;
            }
        }

        if (!$hasPermission) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat detail penilaian ini.');
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
     * Menyimpan penyesuaian (target, bobot, scoring rules) atau skor penilaian.
     */
    public function update(Request $request, KpiAssessment $kpiAssessment)
    {
        $user = Auth::user();
        $action = $request->input('action', 'submit'); // 'save_draft' atau 'submit'

        DB::beginTransaction();
        try {
            // === Tahap 1: Penyesuaian Target, Bobot, dan Aturan Skor ===
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
                    return back()->with('error', 'Total bobot harus tepat 100%.')->withInput();
                }

                foreach ($validated['items'] as $itemId => $data) {
                    $assessmentItem = $kpiAssessment->assessmentItems()->findOrFail($itemId);
                    $rules = $data['rules'] ?? [];

                    $indicatorName = $assessmentItem->indicator?->indicator_name ?? 'Indikator Tidak Diketahui';

                    // ✅ Tambahan: validasi aturan scoring sesuai ketentuan
                    if (count($rules) === 2) {
                        // target harus sama dengan value1 kedua aturan
                        $values = collect($rules)->pluck('value1')->unique();
                        if (!$values->contains($data['target'])) {
                            DB::rollBack();
                            return back()->with('error', "Target dan kedua nilai aturan scoring harus sama untuk indikator {$indicatorName}")->withInput();
                        }
                    } elseif (count($rules) >= 3) {
                        // cari aturan dengan score = 10
                        $ruleTen = collect($rules)->firstWhere('score', 10);
                        if ($ruleTen && $data['target'] != $ruleTen['value1']) {
                            DB::rollBack();
                            return back()->with('error', "Target dan aturan scoring yang bernilai 10 harus sama untuk indikator {$indicatorName}")->withInput();
                        }
                    }

                    // cek aturan between agar selaras
                    foreach ($rules as $rule) {
                        if ($rule['operator'] === 'between') {
                            $val1 = (float) $rule['value1'];
                            $val2 = (float) $rule['value2'];

                            // pastikan ada aturan lain yg menggunakan batasan ini
                            $allValues = collect($rules)->pluck('value1')->merge(collect($rules)->pluck('value2'))->filter();
                            if (!$allValues->contains($val1) || !$allValues->contains($val2)) {
                                DB::rollBack();
                                return back()->with('error', "Aturan 'between' untuk indikator {$indicatorName} harus sesuai dengan batas nilai aturan scoring lainnya")->withInput();
                            }
                        }
                    }

                    // Simpan target & bobot
                    $assessmentItem->update([
                        'target' => $data['target'],
                        'weight' => $data['weight']
                    ]);

                    // Replace aturan scoring lama
                    $assessmentItem->scoringRules()->delete();
                    if (!empty($rules)) {
                        foreach ($rules as $ruleData) {
                            $assessmentItem->scoringRules()->create($ruleData);
                        }
                    }
                }

                // Tambahkan peserta (karyawan) dan update status
                $kpiAssessment->participants()->firstOrCreate(
                    ['assessor_id' => $kpiAssessment->employee->user_id],
                    ['role' => 'self']
                );
                $kpiAssessment->update(['status' => 'Penilaian Diri']);

                DB::commit();
                return redirect()
                    ->route('kpi-assessments.show', $kpiAssessment->id)
                    ->with('success', 'Penyesuaian berhasil disimpan. Karyawan sekarang bisa mengisi penilaian diri.');
            }

            // TAHAP 2: Menyimpan Penilaian Diri atau Atasan (dengan skor otomatis)
            $participant = $kpiAssessment->participants()->where('assessor_id', $user->id)->firstOrFail();

            // MODIFIKASI: Hapus validasi 'score' karena tidak lagi diinput manual
            $validatedData = $request->validate([
                'items' => 'required|array',
                'items.*.achievement_input' => 'nullable|string|max:255', // Ini jadi input utama
                'notes' => 'nullable|string',
            ]);

            foreach ($validatedData['items'] as $itemId => $itemData) {
                $assessmentItem = $kpiAssessment->assessmentItems()->with('scoringRules')->findOrFail($itemId);

                // MODIFIKASI: Hitung skor secara otomatis
                $calculatedScore = 0; // Default score
                if (is_numeric($itemData['achievement_input'])) {
                    $calculatedScore = $this->calculateScoreFromRules(
                        $itemData['achievement_input'],
                        $assessmentItem->scoringRules
                    );
                }

                // Simpan input pencapaian dan skor hasil kalkulasi
                $assessmentItem->scores()->updateOrCreate(
                    ['participant_id' => $participant->id],
                    [
                        'achievement_input' => $itemData['achievement_input'],
                        'score' => $calculatedScore // Gunakan skor hasil perhitungan
                    ]
                );
            }

            if ($action === 'submit') {
                $periodEnd = $kpiAssessment->period->end_date; // pastikan relasi period sudah ada
                $daysRemaining = now()->diffInDays($periodEnd, false);

                if ($daysRemaining > 5) {
                    DB::rollBack();
                    return back()->with(
                        'error',
                        "Penilaian hanya bisa disubmit ketika periode tersisa 5 hari atau kurang. Saat ini masih {$daysRemaining} hari."
                    )->withInput();
                }

                $participant->update(['status' => 'Selesai', 'notes' => $validatedData['notes'] ?? null]);
                $this->updateAssessmentStatus($kpiAssessment);
                $successMessage = 'Penilaian berhasil diserahkan.';
            } else { // 'save_draft'
                $participant->update(['notes' => $validatedData['notes'] ?? null]);
                $successMessage = 'Draft penilaian berhasil disimpan.';
            }

            DB::commit();
            return redirect()->route('kpi-assessments.index')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Memperbarui status alur kerja penilaian.
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
     * Menghitung skor akhir berdasarkan aturan skor yang sudah disesuaikan.
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
                // MODIFIKASI: Logika ini sudah benar, kita pastikan untuk selalu menggunakannya.
                $calculatedScore = $this->calculateScoreFromRules(
                    $finalScoreRecord->achievement_input,
                    $item->scoringRules // Gunakan aturan dari assessment, bukan template
                );

                $itemScoreWeighted = ($calculatedScore) * ($item->weight / 100);

                // Update item penilaian dengan data final
                $item->update([
                    'achievement' => $finalScoreRecord->achievement_input,
                    'final_item_score' => $itemScoreWeighted
                ]);
                $totalScore += $itemScoreWeighted;
            }
        }

        // Update penilaian utama dengan skor total dan status Selesai
        $assessment->update([
            'final_score' => $totalScore,
            'status' => 'Selesai'
        ]);
    }

    /**
     * Helper untuk menghitung skor berdasarkan pencapaian dan set aturan.
     */
    private function calculateScoreFromRules($achievement, $rules)
    {
        if ($rules->isEmpty()) {
            return 0; // Tidak ada aturan, skor 0
        }
        $achievement = (float) $achievement;
        // Urutkan berdasarkan skor tertinggi, jadi aturan yang lebih menguntungkan dicek lebih dulu.
        foreach ($rules->sortByDesc('score') as $rule) {
            $value1 = (float) $rule->value1;
            $value2 = isset($rule->value2) ? (float) $rule->value2 : null;

            switch ($rule->operator) {
                case '<':
                    if ($achievement < $value1)
                        return $rule->score;
                    break;
                case '<=':
                    if ($achievement <= $value1)
                        return $rule->score;
                    break;
                case '=':
                    if ($achievement == $value1)
                        return $rule->score;
                    break;
                case '>=':
                    if ($achievement >= $value1)
                        return $rule->score;
                    break;
                case '>':
                    if ($achievement > $value1)
                        return $rule->score;
                    break;
                case 'between':
                    if ($achievement >= $value1 && $achievement <= $value2)
                        return $rule->score;
                    break;
            }
        }
        return 0; // Skor default jika tidak ada aturan yang cocok
    }

    /**
     * Helper: menutup assessment kadaluarsa & buat baru dari awal
     */
    private function closeExpiredAssessments()
    {
        $now = \Carbon\Carbon::now();

        // Ambil assessment yang periodenya sudah lewat tapi belum kadaluarsa
        $expiredAssessments = KpiAssessment::whereHas('period', function ($q) use ($now) {
            $q->where('end_date', '<', $now);
        })->whereNotIn('status', ['Kadaluarsa', 'Selesai'])->get();

        foreach ($expiredAssessments as $assessment) {
            DB::transaction(function () use ($assessment) {
                // Tandai assessment lama sebagai kadaluarsa
                $assessment->update(['status' => 'Kadaluarsa']);

                // Buat assessment baru di periode berikutnya
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
                }
            });
        }
    }

    /**
     * Deteksi tipe periode dari label unik (Mingguan, Bulanan, dst.)
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
     * Buat periode berikutnya dari periode lama
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

            case 'per_6_bulan': // Semester
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
