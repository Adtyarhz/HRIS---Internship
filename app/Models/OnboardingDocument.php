<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingDocument extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'division_id',
        'is_template',
        'is_active',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /* =========================
     | GLOBAL / LOCAL SCOPES
     ========================= */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGeneral($query)
    {
        return $query->whereNull('division_id');
    }

    public function scopeForDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }
}

