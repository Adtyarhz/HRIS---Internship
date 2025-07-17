<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'address',
        'resume_file',
        'applied_position',
        'last_education',
        'origin',
        'gpa_score',
        'division_id',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    public function recruitmentProgresses()
    {
        return $this->hasMany(RecruitmentProgress::class);
    }

    public function interviewSchedules()
    {
        return $this->hasMany(InterviewSchedule::class);
    }
}

