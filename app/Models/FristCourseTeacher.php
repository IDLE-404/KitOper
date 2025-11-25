<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FristCourseTeacher extends Model
{
    protected $table = 'frist_course_teachers';

    protected $fillable = [
        'initials',
        'teacher_name',
    ];
}
