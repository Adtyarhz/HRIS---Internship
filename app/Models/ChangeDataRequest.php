<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeDataRequest extends Model
{
    use HasFactory;

    protected $table = 'change_data_requests';

    protected $fillable = [
        'model',
        'model_id',
        'action',
        'changes',
        'status',
        'status_notes',
        'requested_by',
        'checked_by',
        'approved_by',
        'rejected_by',
        'checked_at',
        'approved_at',
        'rejected_at',
        'applied_at',
        'failed_at',
        'expired_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'applied_at' => 'datetime',
        'failed_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    // Relasi ke User
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Helper untuk mendapatkan nama pendek dari model yang terpengaruh.
     * e.g., 'App\Models\Employee' menjadi 'Employee'.
     */
    public function getModelShortNameAttribute(): string
    {
        return class_basename($this->model);
    }
}