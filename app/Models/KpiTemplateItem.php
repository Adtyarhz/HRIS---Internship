<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_template_id',
        'kpi_indicator_id',
        'type',
        'weight',
        'default_target',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(KpiTemplate::class, 'kpi_template_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(KpiIndicator::class, 'kpi_indicator_id');
    }

    public function scoringRules(): HasMany
    {
        return $this->hasMany(KpiScoringRule::class);
    }
}