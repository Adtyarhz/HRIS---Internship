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
    public static function notifyNewSession(KpiPeriod $period, User $atasan, $subordinates)
    {
        $subordinateNames = [];

        foreach ($subordinates as $bawahan) {
            if ($bawahan) { // Safely skip any null entries
                $bawahan->notify(new KpiNotification(
                    "A new KPI session has started for the {$period->period_name} period. Please check your targets."
                ));
                if ($bawahan->employee) {
                    $subordinateNames[] = $bawahan->employee->full_name;
                }
            }
        }

        if (!empty($subordinateNames)) {
            $atasan->notify(new KpiNotification(
                "KPI session for " . implode(', ', $subordinateNames) . " has been created for the {$period->period_name} period. Please set targets and assessment."
            ));
        }
    }

    public static function notifyTargetAdjusted($subordinates)
    {
        foreach ($subordinates as $bawahan) {
            $bawahan->notify(new KpiNotification(
                "Your KPI targets have been set. Please complete your self-assessment."
            ));
        }
    }

    public static function notifySelfSubmitted(User $atasanPositionId, User $bawahan, KpiPeriod $period)
    {
        $atasanPositionId->notify(new KpiNotification(
            "{$bawahan->employee->full_name} has completed their self-assessment for the {$period->period_name} period."
        ));
    }

    public static function notifySupervisorSubmitted(User $bawahan, $hrUsers, KpiPeriod $period)
    {
        $bawahan->notify(new KpiNotification(
            "Your supervisor's assessment for the {$period->period_name} period is complete. Final results are now available."
        ));

        foreach ($hrUsers as $hr) {
            $hr->notify(new KpiNotification(
                "KPI assessment for the {$period->period_name} period for {$bawahan->employee->full_name} has been completed."
            ));
        }
    }

    public static function notifyExpired(KpiPeriod $old, KpiPeriod $new, $atasanPositionId, $subordinates)
    {
        foreach ($subordinates as $bawahan) {
            $bawahan->notify(new KpiNotification(
                "The assessment for the {$old->name} period has been closed. A new assessment for the {$new->name} period has been created."
            ));
        }

        $atasanPositionId->notify(new KpiNotification(
            "The {$old->name} period assessment has been closed. The system has created a new period: {$new->name}."
        ));
    }

    public static function notifyReminder(User $bawahan, int $daysRemaining)
    {
        $bawahan->notify(new KpiNotification(
            "The KPI assessment period will end in {$daysRemaining} days. Please submit your assessment."
        ));
    }

    public static function send(User $user, string $message)
    {
        // Use Laravel Notification
        Notification::send($user, new KpiReminderNotification($message));

        // For debugging/logging
        Log::info("KPI reminder sent to {$user->name}: {$message}");
    }
}