<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PhpParser\Node\Expr\Empty_;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'insurance_number',
        'insurance_type',
        'start_date',
        'expiry_date',
        'status',
        'insurance_file',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}