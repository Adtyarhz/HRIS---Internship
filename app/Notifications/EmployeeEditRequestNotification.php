<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmployeeEditRequestNotification extends Notification
{
    use Queueable;

    protected string $employeeName;
    protected int $requestId;
    protected ?string $gender;

    public function __construct(string $employeeName, int $requestId, ?string $gender = null)
    {
        $this->employeeName = $employeeName;
        $this->requestId = $requestId;
        $this->gender = $gender;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $gender = strtolower(trim($this->gender ?? ''));

        $pronoun = match ($gender) {
            'laki-laki' => 'his',
            'perempuan' => 'her',
            default => 'their',
        };

        return [
            'employee_name' => $this->employeeName,
            'request_id' => $this->requestId,
            'message' => "{$this->employeeName} has requested to update {$pronoun} personal information.",
            'status' => 'waiting',
        ];
    }
}
