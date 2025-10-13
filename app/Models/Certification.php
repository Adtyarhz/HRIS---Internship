<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'cost' => 'decimal:2'
    ];

    // ✅ Format tanggal YYYY-MM-DD
    protected function dateObtained(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function expiryDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function certificationMaterials(): HasMany
    {
        return $this->hasMany(CertificationMaterial::class, 'certification_id');
    }
}
