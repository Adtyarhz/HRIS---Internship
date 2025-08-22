<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'model',
        'model_id',
        'method',
        'original_data',
        'changed_data',
        'status',
        'requested_at',
        'approved_by',
    ];

    protected $casts = [
        'original_data' => 'array',
        'changed_data'  => 'array',
        'requested_at'  => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi dinamis ke model yang diedit
     */
    public function editable()
    {
        return $this->morphTo(__FUNCTION__, 'model', 'model_id');
    }
    
}
