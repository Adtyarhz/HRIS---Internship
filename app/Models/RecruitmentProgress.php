<?php

namespace App\Models;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class RecruitmentProgress extends Model
{
    use HasFactory;
    protected $table = 'recruitment_progresses';

    protected $fillable = [
        'applicant_id',
        'stage',
        'offering_status',
        'status_date',
        'notes',
        'rejected_reason',
        'contract_type',
        'test_result',
        'result_file',
        'score',
        'slik_recap',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
    public function userTests() 
    { 
        return $this->hasMany(UserTest::class); 
    }
}

