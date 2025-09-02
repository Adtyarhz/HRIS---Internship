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

class KpiAssessmentController extends Controller
{
    /**
     * Menampilkan dashboard penilaian.
     */
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

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
            $activePeriods = KpiPeriod::where('status', 'Aktif')->orderBy('start_date', 'desc')->get();
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
                $template = KpiTemplate::with('templateItems')->findOrFail($templateId);

                if ($employee->position_id != $template->position_id) {
                    throw new \Exception("Template '{$template->template_name}' tidak sesuai untuk jabatan {$employee->position->title}.");
                }
                $existing = KpiAssessment::where('employee_id', $employeeId)
                                         ->where('kpi_period_id', $validatedData['kpi_period_id'])
                                         ->exists();
                if ($existing) continue;

                $assessment = KpiAssessment::create([
                    'employee_id' => $employee->id,
                    'kpi_period_id' => $validatedData['kpi_period_id'],
                    'primary_supervisor_id' => Auth::id(),
                    'status' => 'Penyesuaian Target',
                ]);

                foreach ($template->templateItems as $item) {
                    $assessment->assessmentItems()->create([
                        'kpi_indicator_id' => $item->kpi_indicator_id,
                        'weight' => $item->weight,
                        'target' => $item->default_target,
                    ]);
                }
                
                // Tambahkan peserta atasan dulu untuk penyesuaian
                $assessment->participants()->create(['assessor_id' => Auth::id(), 'role' => 'direct_supervisor']);
            }
            DB::commit();
            return redirect()->route('kpi-assessments.index')->with('success', 'Sesi penilaian berhasil dibuat. Silakan sesuaikan target dan bobot.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat sesi: ' . $e->getMessage())->withInput();
        }
    }
    
    public function show(KpiAssessment $kpiAssessment)
    {
        $user = Auth::user();
        $isParticipant = $kpiAssessment->participants()->where('assessor_id', $user->id)->exists();
        $isSupervisor = $user->id === $kpiAssessment->primary_supervisor_id;
        if (!$isParticipant && !$isSupervisor) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        $kpiAssessment->load('assessmentItems.indicator', 'assessmentItems.scores.participant', 'participants.assessor', 'employee', 'period');
        return view('kpi.assessments.show', compact('kpiAssessment'));
    }

    /**
     * Menyimpan skor dari karyawan atau atasan.
     */
    public function update(Request $request, KpiAssessment $kpiAssessment)
    {
        $user = Auth::user();
        $action = $request->input('action', 'submit'); // 'save_draft' atau 'submit'
        
        DB::beginTransaction();
        try {
            if ($kpiAssessment->status === 'Penyesuaian Target') {
                $validated = $request->validate([
                    'items' => 'required|array',
                    'items.*.target' => 'required|string|max:255',
                    'items.*.weight' => 'required|numeric|min:0|max:100',
                ]);
                
                // Validasi total bobot = 100%
                $totalWeight = array_sum(array_column($validated['items'], 'weight'));
                if ($totalWeight != 100) {
                    throw new \Exception('Total bobot harus tepat 100%.');
                }

                foreach ($validated['items'] as $itemId => $data) {
                    $assessmentItem = $kpiAssessment->assessmentItems()->findOrFail($itemId);
                    $assessmentItem->update([
                        'target' => $data['target'],
                        'weight' => $data['weight']
                    ]);
                }
                
                // Setelah target disimpan, tambahkan peserta self dan ubah status
                $kpiAssessment->participants()->create(['assessor_id' => $kpiAssessment->employee->user_id, 'role' => 'self']);
                $kpiAssessment->update(['status' => 'Penilaian Diri']);
                
                DB::commit();
                return redirect()->route('kpi-assessments.show', $kpiAssessment->id)->with('success', 'Penyesuaian target dan bobot berhasil disimpan. Karyawan sekarang bisa mengisi penilaian diri.');
            }

            // KONDISI: Menyimpan Penilaian Diri atau Atasan
            $participant = $kpiAssessment->participants()->where('assessor_id', $user->id)->firstOrFail();
            $validatedData = $request->validate([
                'items' => 'required|array',
                'items.*.achievement_input' => 'nullable|string|max:255',
                'items.*.score' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            foreach ($validatedData['items'] as $itemId => $itemData) {
                $assessmentItem = $kpiAssessment->assessmentItems()->findOrFail($itemId);
                $assessmentItem->scores()->updateOrCreate(
                    ['participant_id' => $participant->id],
                    ['achievement_input' => $itemData['achievement_input'], 'score' => $itemData['score']]
                );
            }

            if ($action === 'submit') {
                $participant->update(['status' => 'Selesai', 'notes' => $validatedData['notes']]);
                $this->updateAssessmentStatus($kpiAssessment);
                $successMessage = 'Penilaian berhasil disimpan.';
            } else {
                $participant->update(['notes' => $validatedData['notes']]);
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
        $assessment->refresh(); // Muat ulang data terbaru
        
        $allParticipants = $assessment->participants;
        $completedParticipants = $allParticipants->where('status', 'Selesai');

        if ($completedParticipants->count() === $allParticipants->count()) {
            // Jika semua sudah menilai, finalisasi
            $this->finalizeAssessment($assessment);
        } else {
            // Jika belum semua, update ke status selanjutnya
            $nextStatus = 'Penilaian Atasan Langsung';
            if ($allParticipants->where('role', 'self')->first()->status === 'Selesai') {
                $nextStatus = 'Penilaian Atasan Langsung';
            }
            if ($allParticipants->where('role', 'direct_supervisor')->first()->status === 'Selesai') {
                $nextStatus = 'Penilaian Atasan Tidak Langsung';
            }
            $assessment->update(['status' => $nextStatus]);
        }
    }

    /**
     * Menghitung dan menyimpan skor akhir.
     */
    protected function finalizeAssessment(KpiAssessment $assessment)
    {
        $totalScore = 0;

        foreach ($assessment->assessmentItems as $item) {
            // Ambil skor dari supervisor utama sebagai nilai final
            $supervisorParticipant = $assessment->participants()->where('role', 'direct_supervisor')->first();
            $finalScoreRecord = $item->scores()->where('participant_id', $supervisorParticipant->id)->first();
            
            if ($finalScoreRecord) {
                $finalAchievement = $finalScoreRecord->achievement_input;
                $finalScore = $finalScoreRecord->score;

                // Hitung skor item x bobot
                $itemScoreWeighted = ($finalScore / 100) * $item->weight;

                // Update item penilaian dengan data final
                $item->update([
                    'achievement' => $finalAchievement,
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
}