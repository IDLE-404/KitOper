<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldCampPeriod extends Model
{
    protected $table = 'field_camp_periods';

    protected $fillable = [
        'course',
        'group_id',
        'teacher_id',
        'room_id',
        'start_date',
        'end_date',
        'hours_per_day',
    ];
}
