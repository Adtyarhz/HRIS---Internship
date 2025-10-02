<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'requested_by',
        'approved_by',
        'start_datetime',
        'end_datetime',
        'reason',
        'status',
        'approved_at',
    ];

    // 🔗 Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // 🔗 User yang mengajukan
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // 🔗 User yang menyetujui
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // 🔗 Task lembur
    public function tasks()
    {
        return $this->hasMany(OvertimeApplicationTask::class);
    }

    // 🔗 Riwayat aksi
    public function histories()
    {
        return $this->hasMany(OvertimeApplicationHistory::class);
    }

    // 🔗 Notifikasi lembur
    public function notifications()
    {
        return $this->hasMany(OvertimeApplicationNotification::class);
    }
}
