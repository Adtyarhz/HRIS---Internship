<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'certification_name',
        'issuer',
        'description',
        'date_obtained',
        'expiry_date',
        'cost',
        'certificate_file',
    ];

    protected $casts = [
        'date_obtained' => 'date',
        'expiry_date' => 'date',
        'cost' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function certificationMaterial(): HasMany
    {
        return $this->hasMany(CertificationMaterial::class, 'certification_id');
    }
}