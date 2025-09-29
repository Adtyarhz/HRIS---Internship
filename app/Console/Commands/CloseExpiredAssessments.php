<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KpiAssessment;
use App\Models\KpiPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CloseExpiredAssessments extends Command
{
    protected $signature = 'assessments:close-expired';

    protected $description = 'Tutup penilaian yang sudah kadaluarsa, dan buat penilaian baru jika periodenya otomatis';

    public function handle()
    {
        $now = Carbon::now();

        // Ambil assessment yang periodenya sudah lewat tapi belum kadaluarsa/selesai
        $expiredAssessments = KpiAssessment::whereHas('period', function ($q) use ($now) {
            $q->where('end_date', '<', $now);
        })->whereNotIn('status', ['Kadaluarsa', 'Selesai'])->get();

        if ($expiredAssessments->isEmpty()) {
            $this->info("Tidak ada assessment yang kadaluarsa.");
            return;
        }

        foreach ($expiredAssessments as $assessment) {
            DB::transaction(function () use ($assessment) {
                $oldPeriod = $assessment->period;

                // Tandai assessment lama kadaluarsa
                $assessment->update(['status' => 'Kadaluarsa']);

                // Deteksi tipe periode dari nama
                $type = $this->detectPeriodType($oldPeriod->period_name);

                // Kalau tidak otomatis → skip
                if (!$type) {
                    $this->info("Assessment ID {$assessment->id} ditutup (periode manual/khusus, tidak dibuat baru).");
                    return;
                }

                // Buat periode berikutnya
                $newPeriod = $this->getNextPeriod($oldPeriod, $type);

                if (!$newPeriod) {
                    $this->warn("Assessment ID {$assessment->id} ditutup, tapi periode baru belum ada.");
                    return;
                }

                // Buat assessment baru
                $newAssessment = $assessment->replicate();
                $newAssessment->status = 'Penyesuaian Target';
                $newAssessment->final_score = null;
                $newAssessment->kpi_period_id = $newPeriod->id;
                $newAssessment->save();

                // Clone assessment items & scoring rules
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

                $this->info("Assessment ID {$assessment->id} ditutup → assessment baru ID {$newAssessment->id} dibuat untuk periode {$newPeriod->period_name}.");
            });
        }
    }

    /**
     * Deteksi tipe periode dari label
     */
    private function detectPeriodType(string $name): ?string
    {
        if (str_starts_with($name, 'Mingguan')) return 'mingguan';
        if (str_starts_with($name, 'Bulanan')) return 'bulanan';
        if (str_starts_with($name, 'Triwulan')) return 'triwulan';
        if (str_starts_with($name, 'Semester')) return 'per_6_bulan';
        if (str_starts_with($name, 'Periode 4 Bulanan')) return 'per_4_bulan';
        if (str_starts_with($name, 'Tahunan')) return 'tahunan';
        return null;
    }

    /**
     * Buat periode berikutnya berdasarkan periode lama
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
                $label = "Triwulan Q" . $nextStart->quarter . " " . $nextStart->format('Y');
                break;

            case 'per_4_bulan':
                $nextStart = Carbon::parse($oldPeriod->start_date)->addMonths(4)->startOfMonth();
                $nextEnd = $nextStart->copy()->addMonths(3)->endOfMonth();
                $label = "Periode 4 Bulanan ({$nextStart->format('M Y')} - {$nextEnd->format('M Y')})";
                break;

            case 'per_6_bulan':
                $nextStart = Carbon::parse($oldPeriod->start_date)->addMonths(6)->startOfMonth();
                $nextEnd = $nextStart->copy()->addMonths(5)->endOfMonth();
                $semester = ($nextStart->month <= 6) ? 1 : 2;
                $label = "Semester {$semester} {$nextStart->format('Y')}";
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
                    'start_date'  => $nextStart->format('Y-m-d'),
                    'end_date'    => $nextEnd->format('Y-m-d'),
                ],
                [
                    'status' => 'Aktif'
                ]
            );
        }

        return null;
    }
}
