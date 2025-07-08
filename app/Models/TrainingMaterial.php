<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_history_id',
        'file_path',
    ];

    public function trainingHistory(): BelongsTo
    {
        return $this->belongsTo(TrainingHistory::class, 'training_history_id');
    }
}
