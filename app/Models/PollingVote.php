<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollingVote extends Model
{
    protected $fillable = ['polling_option_id', 'user_id'];

    public function pollingOption()
{
    return $this->belongsTo(PollingOption::class);
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
