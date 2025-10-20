<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function careerProjections(): HasMany
    {
        return $this->hasMany(CareerProjection::class, 'created_by');
    }

    public function editRequests(): HasMany
    {
        return $this->hasMany(EmployeeEditRequest::class, 'employee_id');
    }
    
    public function scopeAdmins($query)
    {
        return $query->whereIn(DB::raw('LOWER(role)'), ['hc', 'superadmin']);
    }

    // --- Relasi ke ChangeDataRequest ---

    public function changeDataRequestsMade(): HasMany
    {
        return $this->hasMany(ChangeDataRequest::class, 'requested_by');
    }

    public function changeDataRequestsChecked(): HasMany
    {
        return $this->hasMany(ChangeDataRequest::class, 'checked_by');
    }

    public function changeDataRequestsApproved(): HasMany
    {
        return $this->hasMany(ChangeDataRequest::class, 'approved_by');
    }

    /**
     * Relasi untuk request yang ditolak oleh user ini.
     */
    public function changeDataRequestsRejected(): HasMany
    {
        return $this->hasMany(ChangeDataRequest::class, 'rejected_by');
    }
}
