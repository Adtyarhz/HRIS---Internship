<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'position_id',
        'is_active',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function templateItems(): HasMany
    {
        return $this->hasMany(KpiTemplateItem::class);
    }
}