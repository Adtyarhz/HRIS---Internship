<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiScoringRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_template_item_id',
        'operator',
        'value1',
        'value2',
        'score',
    ];

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(KpiTemplateItem::class, 'kpi_template_item_id');
    }
}