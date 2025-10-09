<?php

namespace App\Notifications;

use App\Models\InterviewSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class InterviewScheduleNotification extends Notification
{
    use Queueable;

    public function __construct(public InterviewSchedule $schedule) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $type = ucfirst($this->schedule->interview_type);
        $applicant = $this->schedule->applicant->full_name;

        // Format tanggal agar rapi dan dalam zona waktu Asia/Jakarta
        $formattedDate = Carbon::parse($this->schedule->interview_date)
            ->locale('id')
            ->timezone('Asia/Jakarta')
            ->translatedFormat('d F Y, H:i');

        return [
            'title' => "Jadwal Interview {$type} Baru",
            'message' => "The interview for <b>{{$applicant}}</b> has been scheduled for <b>{{$formattedDate}} WIB</b>.",
            'url' => route('interview-schedule.show', [$this->schedule->applicant_id, $this->schedule->id]),
        ];
    }
}
