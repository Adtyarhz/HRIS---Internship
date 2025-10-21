<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'certificate_file',
    ];

    protected $casts = [
        'cost' => 'decimal:2'
    ];

    // ✅ Format tanggal
    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function trainingMaterials(): HasMany
    {
        return $this->hasMany(TrainingMaterial::class, 'training_history_id');
    }
}
