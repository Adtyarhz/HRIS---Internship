<?php

namespace App\Models;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'interview_type',
        'interview_date',
        'interviewer_id',
        'location',
        'result',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}

