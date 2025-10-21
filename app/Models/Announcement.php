<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';

    protected $fillable = [
        'created_by',
        'title',
        'announcement_type',
        'label',
        'content',
        'attachment_file',
        'external_link',
    ];

     protected $casts = [
        'external_link' => 'array', // <-- ini penting
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function polling()
    {
        return $this->hasOne(Polling::class);
    }
    public function targetDivisions()
    {
        return $this->belongsToMany(Division::class, 'announcement_division');
    }
}