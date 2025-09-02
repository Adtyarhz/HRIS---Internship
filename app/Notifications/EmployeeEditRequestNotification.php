<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmployeeEditRequestNotification extends Notification
{
    use Queueable;

    protected string $employeeName;
    protected int $requestId;

    public function __construct(string $employeeName, int $requestId)
    {
        $this->employeeName = $employeeName;
        $this->requestId = $requestId;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'employee_name' => $this->employeeName,
            'request_id' => $this->requestId,
            'message' => "{$this->employeeName} mengajukan request edit data.",
            'status' => 'waiting',
        ];
    }
}
