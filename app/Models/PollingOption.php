<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollingOption extends Model
{
    use HasFactory;
    protected $fillable = ['polling_id', 'option_text'];

    public function polling()
    {
        return $this->belongsTo(Polling::class);
    }

    public function votes()
    {
        return $this->hasMany(PollingVote::class);
    }
}
