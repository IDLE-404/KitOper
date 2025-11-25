<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleReplacement extends Model
{
    protected $fillable = [
        'group_id',
        'study_day',
        'lesson_number',
        'week_mode',
        'subject_id',
        'absent_teacher_id',
        'replacement_teacher_id',
        'room_id',
        'comment',
    ];
}
