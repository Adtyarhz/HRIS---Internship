<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'company_name',
        'company_address',
        'company_phone',
        'position_title',
        'start_date',
        'end_date',
        'responsibilities',
        'reason_to_leave',
        'last_salary',
        'reference_letter_file',
        'salary_slip_file',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}