<?php

namespace App\Services;

use App\Models\ChangeDataRequest;
use App\Models\User;
use Exception;
use App\Notifications\ApprovalRequestCheckedNotification;
use App\Notifications\ApprovalRequestApprovedNotification;
use App\Notifications\ApprovalRequestRejectedNotification;

class CheckerWorkflowService
{
    /**
     * Aksi oleh Checker (staf HC lain).
     */
    public static function check(ChangeDataRequest $cdr, User $checker, ?string $notes = null): ChangeDataRequest
    {
        if ($cdr->status !== 'pending') {
            throw new Exception('Request tidak dalam status pending.');
        }
        if ($cdr->requested_by === $checker->id) {
            throw new Exception('Pembuat request tidak boleh menjadi checker.');
        }

        $cdr->update([
            'status' => 'checked',
            'checked_by' => $checker->id,
            'checked_at' => now(),
            'status_notes' => $notes,
        ]);

        // V-- NOTIFIKASI DIAKTIFKAN --V
        self::notifyApprovers($cdr);
        return $cdr;
    }

    /**
     * Aksi oleh Approver (HC & GA Manager).
     */
    public static function approve(ChangeDataRequest $cdr, User $approver, ?string $notes = null): ChangeDataRequest
    {
        if ($cdr->status !== 'checked') {
            throw new Exception('Request harus berstatus "checked" sebelum bisa disetujui.');
        }
        
        $positionName = config('approval.approver_position_name', 'HC & GA Manager');
        if (optional($approver->employee)->position->title !== $positionName) {
            throw new Exception('Hanya user dengan posisi yang sesuai yang dapat melakukan approval.');
        }

        $cdr->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'status_notes' => $notes,
        ]);
        
        if (config('approval.auto_apply', true)) {
            ApprovalWorkflowService::apply($cdr);
        }

        // V-- NOTIFIKASI DIAKTIFKAN --V
        // Kirim notifikasi ke pembuat request
        optional($cdr->requester)->notify(new ApprovalRequestApprovedNotification($cdr));

        return $cdr;
    }

    /**
     * Aksi penolakan oleh Checker atau Approver.
     */
    public static function reject(ChangeDataRequest $cdr, User $actor, string $reason): ChangeDataRequest
    {
        if (!in_array($cdr->status, ['pending', 'checked'])) {
            throw new Exception('Request ini tidak dapat ditolak.');
        }

        $cdr->update([
            'status' => 'rejected',
            'rejected_by' => $actor->id,
            'rejected_at' => now(),
            'status_notes' => $reason,
        ]);

        // V-- NOTIFIKASI DIAKTIFKAN --V
        // Kirim notifikasi ke pembuat request
        optional($cdr->requester)->notify(new ApprovalRequestRejectedNotification($cdr));

        return $cdr;
    }

    /**
     * Kirim notifikasi ke semua kandidat Approver.
     */
    protected static function notifyApprovers(ChangeDataRequest $cdr): void
    {
        $channels = config('approval.notification_channels', ['database', 'mail']); // <-- Menambahkan mail sebagai default
        $approverPosition = config('approval.approver_position_name', 'HC & GA Manager');

        $approvers = User::where('role', 'hc')
            ->whereHas('employee.position', function ($query) use ($approverPosition) {
                $query->where('title', $approverPosition);
            })->get();

        foreach ($approvers as $user) {
            // V-- NOTIFIKASI DIAKTIFKAN --V
            $user->notify(new ApprovalRequestCheckedNotification($cdr));
        }
    }
}

