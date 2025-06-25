<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

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
        'age',
        'marital_status',
        'dependents',
        'ktp_address',
        'current_address',
        'phone_number',
        'email',
        'status',
        'employee_type',
        'hire_date',
        'separation_date',
        'division_id',
        'position_id',
        'user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'separation_date' => 'date',
    ];

    protected $with = ['user', 'division', 'position'];

    protected $appends = ['age'];

    public function getAgeAttribute()
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : null;
    }

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

    public function certification(): HasMany
    {
        return $this->hasMany(Certification::class, 'employee_id');
    }

    public function trainingHistory(): HasMany
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

    public function familyDependent(): HasMany
    {
        return $this->hasMany(FamilyDependent::class, 'employee_id');
    }
}