<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiAssessmentItemScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_assessment_item_id',
        'participant_id',
        'achievement_input',
        'score',
    ];

    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(KpiAssessmentItem::class, 'kpi_assessment_item_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(KpiAssessmentParticipant::class, 'participant_id');
    }
}