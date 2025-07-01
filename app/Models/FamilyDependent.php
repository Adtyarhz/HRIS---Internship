<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyDependent extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'contact_name',
        'relationship',
        'phone_number',
        'address',
        'city',
        'province',
    ];
    
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
