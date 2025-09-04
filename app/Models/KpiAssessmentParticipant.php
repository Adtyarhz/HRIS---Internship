<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiAssessmentParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_assessment_id',
        'assessor_id',
        'role',
        'status',
        'notes',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(KpiAssessment::class, 'kpi_assessment_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function itemScores(): HasMany
    {
        return $this->hasMany(KpiAssessmentItemScore::class, 'participant_id');
    }
}