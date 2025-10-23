<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ChangeDataRequest;

class ApprovalRequestApprovedNotification extends Notification
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
            'message' => "Request #{$this->cdr->id} telah disetujui dan diterapkan ke sistem.",
            'model' => $this->cdr->model,
            'status' => $this->cdr->status,
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Request Approved & Applied')
            ->line("Request #{$this->cdr->id} untuk {$this->cdr->model} telah disetujui oleh HC Manager.")
            ->action('Lihat Detail', url('/approvals/' . $this->cdr->id))
            ->line('Perubahan telah diterapkan ke sistem.');
    }
}
