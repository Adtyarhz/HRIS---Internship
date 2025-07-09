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
        'interviewer',
        'location',
        'result',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}

