<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'created_by',
        'title',
        'announcement_type',
        'label',
        'content',
        'attachment_file',
        'external_link',
    ];

    /**
     * Relasi ke user yang membuat pengumuman.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi jika pengumuman bertipe polling.
     */
    public function polling()
    {
        return $this->hasOne(Polling::class);
    }
}