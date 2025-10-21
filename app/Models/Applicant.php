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
    public function getCurrentStageAttribute()
{
    $stages = [
        'cv_screening',
        'general_knowledge_test',
        'user_assessment',
        'hc_interview',
        'bod_interview',
        'offering_letter',
    ];

    $progresses = $this->recruitmentProgresses()->get()->keyBy('stage');

    // Jika belum ada data sama sekali
    if ($progresses->isEmpty()) {
        return 'cv_screening';
    }

    // Jika ada yang rejected
    $rejected = $progresses->firstWhere('offering_status', 'rejected');
    if ($rejected) {
        return $rejected->stage;
    }

    // Jika ada yang in_progress
    $inProgress = $progresses->firstWhere('offering_status', 'in_progress');
    if ($inProgress) {
        return $inProgress->stage;
    }

    // Jika semua sebelumnya accepted tapi belum lanjut ke stage berikutnya
    foreach ($stages as $stage) {
        if (!$progresses->has($stage)) {
            return $stage; // stage berikutnya yang belum diisi
        }
    }

    // Jika semua sudah ada dan offering_letter sudah accepted
    return 'offering_letter';
}

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'applied_position');
    }
    public function employee()
    {
        return $this->hasOne(Employee::class, 'applicant_id');
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
