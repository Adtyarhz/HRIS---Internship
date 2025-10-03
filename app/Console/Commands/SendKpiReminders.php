<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KpiAssessment;
use Carbon\Carbon;
use App\Services\KpiNotifier;

class SendKpiReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan kpi:send-reminder
     */
    protected $signature = 'kpi:send-reminder';

    /**
     * The console command description.
     */
    protected $description = 'Kirim reminder KPI kepada atasan & bawahan 5 hari terakhir sebelum deadline penilaian selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now();

        // Ambil assessment yang masih berjalan
        $assessments = KpiAssessment::whereIn('status', ['Self Assessment', 'Penilaian Atasan'])
            ->with(['period', 'employee.user', 'supervisor'])
            ->get();

        foreach ($assessments as $assessment) {
            $endDate = Carbon::parse($assessment->period->end_date);
            $daysLeft = $today->diffInDays($endDate, false);

            // Hanya kirim kalau tinggal ≤ 5 hari dan belum lewat
            if ($daysLeft >= 0 && $daysLeft <= 5) {
                if ($assessment->status === 'Self Assessment') {
                    // Reminder untuk bawahan
                    KpiNotifier::send(
                        $assessment->employee->user,
                        "Segera lengkapi self assessment KPI Anda. Deadline tinggal {$daysLeft} hari lagi."
                    );

                    // Reminder untuk atasan
                    if ($assessment->supervisor) {
                        KpiNotifier::send(
                            $assessment->supervisor,
                            "Segera ingatkan bawahan Anda ({$assessment->employee->user->name}) untuk menyelesaikan self assessment KPI. Deadline tinggal {$daysLeft} hari lagi."
                        );
                    }

                } elseif ($assessment->status === 'Penilaian Atasan') {
                    // Reminder khusus untuk atasan
                    if ($assessment->supervisor) {
                        KpiNotifier::send(
                            $assessment->supervisor,
                            "Silakan nilai KPI bawahan Anda ({$assessment->employee->user->name}). Deadline tinggal {$daysLeft} hari lagi."
                        );
                    }
                }
            }
        }

        $this->info('Reminder KPI berhasil dikirim.');
    }
}
