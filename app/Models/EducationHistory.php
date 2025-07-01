<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'education_level',
        'institution_name',
        'institution_address',
        'major',
        'start_year',
        'end_year',
        'gpa_or_score',
        'certificate_number',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}