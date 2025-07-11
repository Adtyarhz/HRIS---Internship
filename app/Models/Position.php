<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    public function careerHistories(): HasMany
    {
        return $this->hasMany(CareerHistory::class, 'position_id');
    }

    public function careerProjections(): HasMany
    {
        return $this->hasMany(CareerProjection::class, 'projected_position_id');
    }
}