<?php

namespace App\Notifications;

use App\Models\OvertimeApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OvertimeApplicationNotification extends Notification
{
    use Queueable;

    protected $application;
    protected $message;
    protected $url;

    public function __construct(OvertimeApplication $application, string $message, string $url = null)
    {
        $this->application = $application;
        $this->message = $message;
        $this->url = $url ?: route('overtime-applications.show', $application->id);
    }

    public function via($notifiable)
    {
        return ['database']; // bisa tambah 'mail' kalau perlu email
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'application_id' => $this->application->id,
            'status' => $this->application->status,
        ];
    }
}
