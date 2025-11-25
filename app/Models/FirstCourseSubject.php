<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirstCourseSubject extends Model
{
    protected $table = 'first_course_subjects';

    protected $fillable = [
        'module_title',
        'module_index',
        'subject_name',
        'name_ru',
        'name_kz',
    ];
}
