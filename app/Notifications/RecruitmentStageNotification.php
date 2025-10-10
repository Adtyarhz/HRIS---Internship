<?php

namespace App\Notifications;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class RecruitmentStageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Applicant $applicant,
        public string $stage,
        public string $status
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $stageName = ucfirst(str_replace('_', ' ', $this->stage));
        return [
            'title' => "Recruitment Stage Updated",
            'message' => "{$this->applicant->full_name}'s stage '{$stageName}' has been marked as {$this->status}.",
            'url' => route('recruitment.stage.show', [$this->applicant->id, $this->stage]),
        ];
    }
}
