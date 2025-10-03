<?php

namespace App\Services;

use App\Models\User;
use App\Models\KpiPeriod;
use App\Notifications\KpiNotification;
use App\Notifications\KpiReminderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class KpiNotifier
{
    public static function notifyNewSession(KpiPeriod $period, User $atasanPositionId, $subordinates)
    {
        foreach ($subordinates as $bawahan) {
            $bawahan->notify(new KpiNotification(
                "Sesi KPI baru telah dimulai untuk periode {$period->period_name}. Silakan cek target Anda."
            ));
        }

        $atasanPositionId->notify(new KpiNotification("Sesi KPI {$bawahan->employee->full_name} berhasil dibuat untuk periode {$period->period_name}. Silakan atur target dan penilaian."));
    }

    public static function notifyTargetAdjusted($subordinates)
    {
        foreach ($subordinates as $bawahan) {
            $bawahan->notify(new KpiNotification(
                "Target KPI Anda sudah ditetapkan. Silakan isi penilaian diri."
            ));
        }
    }

    public static function notifySelfSubmitted(User $atasanPositionId, User $bawahan, KpiPeriod $period)
    {
        $atasanPositionId->notify(new KpiNotification(
            "{$bawahan->employee->full_name} telah menyelesaikan penilaian diri untuk periode {$period->period_name}."
        ));
    }

    public static function notifySupervisorSubmitted(User $bawahan, $hrUsers, KpiPeriod $period)
    {
        $bawahan->notify(new KpiNotification(
            "Penilaian atasan Anda untuk periode {$period->period_name} sudah selesai. Hasil akhir dapat dilihat."
        ));

        foreach ($hrUsers as $hr) {
            $hr->notify(new KpiNotification(
                "Penilaian KPI periode {$period->period_name} untuk {$bawahan->employee->full_name} telah selesai."
            ));
        }
    }

    public static function notifyExpired(KpiPeriod $old, KpiPeriod $new, $atasanPositionId, $subordinates)
    {
        foreach ($subordinates as $bawahan) {
            $bawahan->notify(new KpiNotification(
                "Assessment periode {$old->name} telah ditutup. Assessment baru untuk periode {$new->name} sudah dibuat."
            ));
        }

        $atasanPositionId->notify(new KpiNotification(
            "Assessment periode {$old->name} ditutup, sistem sudah membuat periode baru: {$new->name}."
        ));
    }

    public static function notifyReminder(User $bawahan, int $daysRemaining)
    {
        $bawahan->notify(new KpiNotification(
            "Periode penilaian KPI akan berakhir dalam {$daysRemaining} hari. Silakan submit penilaian Anda."
        ));
    }

    public static function send(User $user, string $message)
    {
        // Bisa pakai Laravel Notification
        Notification::send($user, new KpiReminderNotification($message));

        // Untuk debug/logging
        Log::info("Reminder KPI terkirim ke {$user->name}: {$message}");
    }
}