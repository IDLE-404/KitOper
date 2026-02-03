<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticePeriod extends Model
{
    protected $table = 'practice_periods';

    protected $fillable = [
        'course',
        'group_id',
        'subject_id',
        'type',
        'teacher_id',
        'room_id',
        'start_date',
        'end_date',
        'hours_per_day',
    ];
}
