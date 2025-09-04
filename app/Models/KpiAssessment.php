<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'kpi_period_id',
        'primary_supervisor_id',
        'status',
        'final_score',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'kpi_period_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_supervisor_id');
    }

    public function assessmentItems(): HasMany
    {
        return $this->hasMany(KpiAssessmentItem::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(KpiAssessmentParticipant::class);
    }
}