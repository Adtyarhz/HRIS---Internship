<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nip',
        'npwp',
        'full_name',
        'gender',
        'religion',
        'birth_place',
        'birth_date',
        'marital_status',
        'dependents',
        'ktp_address',
        'current_address',
        'phone_number',
        'email',
        'status',
        'employee_type',
        'office',
        'hire_date',
        'separation_date',
        'cv_file',
        'photo',
        'division_id',
        'position_id',
        'user_id',
    ];

     // ✅ Accessor untuk pastikan keluar string "YYYY-MM-DD"
    protected function birthDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function hireDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function separationDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function getCvFileUrlAttribute()
    {
        return $this->cv_file ? asset('storage/cv/' . $this->cv_file) : null;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/photo/' . $this->photo) : null;
    }

    protected $with = ['user', 'division', 'position'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function educationHistory(): HasMany
    {
        return $this->hasMany(EducationHistory::class, 'employee_id');
    }

    public function workExperience(): HasMany
    {
        return $this->hasMany(WorkExperience::class, 'employee_id');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class, 'employee_id');
    }

    public function trainingHistories(): HasMany
    {
        return $this->hasMany(TrainingHistory::class, 'employee_id');
    }

    public function healthRecord(): HasOne
    {
        return $this->hasOne(HealthRecord::class, 'employee_id');
    }

    public function insurance(): HasMany
    {
        return $this->hasMany(Insurance::class, 'employee_id');
    }

    public function familyDependents(): HasMany
    {
        return $this->hasMany(FamilyDependent::class, 'employee_id');
    }

    public function careerHistories(): HasMany
    {
        return $this->hasMany(CareerHistory::class, 'employee_id');
    }

    public function careerProjections(): HasMany
    {
        return $this->hasMany(CareerProjection::class, 'employee_id');
    }

    public function getDivisionIdAttribute($value)
    {
        if ($this->relationLoaded('position') && $this->position?->division_id) {
            return $this->position->division_id;
        }

        return $value;
    }
}