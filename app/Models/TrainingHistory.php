<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'training_name',
        'provider',
        'description',
        'start_date',
        'end_date',
        'cost',
        'location',
        'certificate_number',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cost' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function trainingMaterial(): HasMany
    {
        return $this->hasMany(TrainingMaterial::class, 'training_id');
    }
}