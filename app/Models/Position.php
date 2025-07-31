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
        'indirect_supervisor_id',
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

    public function indirectSupervisor(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'indirect_supervisor_id');
    }

    public function indirectSubordinates(): HasMany
    {
        return $this->hasMany(Position::class, 'indirect_supervisor_id');
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

    public function getCoordinatesAttribute()
    {
        return [
            'x' => $this->depth * 200,
            'y' => $this->getRelativeYPosition()
        ];
    }

    protected function getRelativeYPosition()
    {
        $siblingCount = Position::where('parent_id', $this->parent_id)->count();
        $siblingIndex = Position::where('parent_id', $this->parent_id)
            ->where('id', '<=', $this->id)
            ->count() - 1;
        return ($siblingIndex * 80) + 50;
    }
}