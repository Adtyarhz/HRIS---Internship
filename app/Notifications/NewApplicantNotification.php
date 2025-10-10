<?php

namespace App\Notifications;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class NewApplicantNotification extends Notification
{
    use Queueable;

    public function __construct(public Applicant $applicant) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Applicant Submitted',
            'message' => "{$this->applicant->full_name} has applied for {$this->applicant->position->title}.",
            'url' => route('applicants.show', $this->applicant->id),
        ];
    }
}
