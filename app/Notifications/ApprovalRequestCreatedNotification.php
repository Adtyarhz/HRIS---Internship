<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ChangeDataRequest;

class ApprovalRequestCreatedNotification extends Notification
{
    use Queueable;

    protected $cdr;
    protected $channels = ['database'];

    public function __construct(ChangeDataRequest $cdr)
    {
        $this->cdr = $cdr;
    }

    public function setChannels($channels)
    {
        $this->channels = $channels;
        return $this;
    }

    public function via($notifiable)
    {
        return $this->channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'cdr_id' => $this->cdr->id,
            'model' => $this->cdr->model,
            'action' => $this->cdr->action,
            'requested_by' => $this->cdr->requested_by,
            'message' => 'Terdapat request perubahan data menunggu checker.'
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('New Approval Request')
            ->line("Change request ({$this->cdr->id}) for {$this->cdr->model} requires your review.")
            ->action('Open Approval Panel', url('/approvals/'.$this->cdr->id))
            ->line('Terima kasih.');
    }
}
