<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'certification_id',
        'file_path',
    ];

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'certification_id');
    }
}
