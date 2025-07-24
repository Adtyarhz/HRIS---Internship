<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'parent_id',
        'depth'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Position::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    public function careerHistories(): HasMany
    {
        return $this->hasMany(CareerHistory::class, 'position_id');
    }

    public function careerProjection(): HasOne
    {
        return $this->hasOne(CareerProjection::class, 'projected_position_id');
    }
}