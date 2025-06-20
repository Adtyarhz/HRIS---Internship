<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollingVote extends Model
{
    use HasFactory;

    protected $fillable = ['polling_option_id', 'created_by'];

    public function pollingOption()
    {
        return $this->belongsTo(PollingOption::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
