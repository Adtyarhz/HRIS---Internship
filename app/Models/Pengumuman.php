<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'user_id',
        'label', // untuk menyimpan label pengumuman
        'judul',
        'isi',
        'tipe',
        'attachment', // untuk menyimpan file PDF/gambar
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function polling()
{
    return $this->hasOne(Polling::class);
}
}
