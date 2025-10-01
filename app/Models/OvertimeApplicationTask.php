<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeApplicationTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_application_id',
        'task_description',
        'is_completed',
        'completed_at',
    ];
    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];
    // 🔗 Relasi ke aplikasi lembur
    public function overtimeApplication()
    {
        return $this->belongsTo(OvertimeApplication::class);
    }
}
