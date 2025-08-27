<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_name',
        'description',
        'measurement_unit',
        'higher_is_better',
    ];
}
