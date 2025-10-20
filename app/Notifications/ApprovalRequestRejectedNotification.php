<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ChangeDataRequest;

class ApprovalRequestRejectedNotification extends Notification
{
    use Queueable;

    protected $cdr;

    public function __construct(ChangeDataRequest $cdr)
    {
        $this->cdr = $cdr;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'cdr_id' => $this->cdr->id,
            'message' => "Request #{$this->cdr->id} ditolak. Mohon periksa alasan penolakan.",
            'model' => $this->cdr->model,
            'status' => $this->cdr->status,
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Request Rejected')
            ->line("Request perubahan data #{$this->cdr->id} telah ditolak.")
            ->action('Lihat Detail', url('/approvals/' . $this->cdr->id))
            ->line('Silakan periksa alasan penolakan pada sistem.');
    }
}
