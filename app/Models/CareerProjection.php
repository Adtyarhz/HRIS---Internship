<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerProjection extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'projected_position_id',
        'timeline',
        'status',
        'readiness_notes',
        'created_by',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function projectedPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'projected_position_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
