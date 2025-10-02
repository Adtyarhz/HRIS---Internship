<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiAssessmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_assessment_id',
        'kpi_indicator_id',
        'weight',
        'target',
        'achievement',
        'final_item_score',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(KpiAssessment::class, 'kpi_assessment_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(KpiIndicator::class, 'kpi_indicator_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(KpiAssessmentItemScore::class);
    }

    public function scoringRules(): HasMany
    {
        return $this->hasMany(KpiAssessmentItemScoringRule::class, 'kpi_assessment_item_id');
    }
}