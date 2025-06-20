<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polling extends Model
{
    protected $fillable = ['pengumuman_id', 'batas_waktu'];

    public function pengumuman()
    {
        return $this->belongsTo(Pengumuman::class);
    }

    public function options()
    {
        return $this->hasMany(PollingOption::class);
    }
}
