<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmployeeEditStatusNotification extends Notification
{
    use Queueable;

    protected $status;
    protected $message;

    public function __construct($status, $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database']; // kita simpan di DB
    }

    public function toDatabase($notifiable)
    {
        return [
            'status' => $this->status,   // approved / rejected / sent
            'message' => $this->message, // teks notifikasi
        ];
    }
}
