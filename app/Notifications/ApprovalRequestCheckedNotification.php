<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ChangeDataRequest;

class ApprovalRequestCheckedNotification extends Notification
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
            'message' => "Request #{$this->cdr->id} telah diverifikasi oleh checker dan menunggu approval HC Manager.",
            'model' => $this->cdr->model,
            'status' => $this->cdr->status,
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Request Menunggu Approval HC Manager')
            ->line("Request perubahan data #{$this->cdr->id} telah diverifikasi oleh checker HC.")
            ->action('Lihat Request', url('/approvals/' . $this->cdr->id))
            ->line('Silakan tinjau dan berikan keputusan.');
    }
}
