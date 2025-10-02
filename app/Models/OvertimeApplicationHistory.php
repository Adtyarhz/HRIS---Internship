<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeApplicationHistory extends Model
{
    use HasFactory;

    public $timestamps = false; // karena hanya pakai created_at

    protected $fillable = [
        'overtime_application_id',
        'action_by',
        'action_type',
        'description',
        'created_at',
    ];

    // 🔗 Relasi ke aplikasi lembur
    public function overtimeApplication()
    {
        return $this->belongsTo(OvertimeApplication::class);
    }

    // 🔗 User yang melakukan aksi
    public function actor()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
