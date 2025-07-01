<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'height',
        'weight',
        'blood_type',
        'known_allergies',
        'chronic_diseases',
        'last_checkup_date',
        'checkup_loc',
        'price_last_checkup',
        'notes',
    ];

    protected $casts = [
        'last_checkup_date' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}