<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeApplicationNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_application_id',
        'recipient_id',
        'message',
        'is_read',
        'read_at',
    ];

    // 🔗 Relasi ke aplikasi lembur
    public function overtimeApplication()
    {
        return $this->belongsTo(OvertimeApplication::class);
    }

    // 🔗 Penerima notifikasi
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
