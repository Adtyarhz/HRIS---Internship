<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polling extends Model
{
    protected $fillable = ['announcement_id', 'deadline', 'created_by'];

    public function pengumuman()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function options()
    {
        return $this->hasMany(PollingOption::class);
    }
}
