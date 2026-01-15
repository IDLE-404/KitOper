<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = [
        'name',
        'start_month',
        'start_day',
        'end_month',
        'end_day',
        'year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'year' => 'integer',
        'start_month' => 'integer',
        'start_day' => 'integer',
        'end_month' => 'integer',
        'end_day' => 'integer',
    ];
}
