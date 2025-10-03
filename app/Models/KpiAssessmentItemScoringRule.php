<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiAssessmentItemScoringRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_assessment_item_id',
        'operator',
        'value1',
        'value2',
        'score',
    ];

    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(KpiAssessmentItem::class, 'kpi_assessment_item_id');
    }
}