<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'recruitment_progress_id', 
        'test_name', 
        'score', 
        'status', 
        'notes', 
        'test_date',
    ];

    public function recruitmentProgress() 
    { 
        return $this->belongsTo(RecruitmentProgress::class); 
    }
}
