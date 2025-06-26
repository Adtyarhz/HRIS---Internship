<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polling extends Model
{
    use HasFactory;
    protected $fillable = ['announcement_id', 'deadline', 'created_by'];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function options()
    {
        return $this->hasMany(PollingOption::class);
    }
}
