<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'division_id');
    }
    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class, 'division_id');
    }

    public function careerHistories(): HasMany
    {
        return $this->hasMany(CareerHistory::class, 'division_id');
    }
    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_division');
    }
}