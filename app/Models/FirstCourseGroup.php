<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirstCourseGroup extends Model
{
    protected $table = 'first_course_group';

    protected $fillable = [
        'group_name',
        'group_number',
        'subgroup',
        'has_subgroups',
    ];
}
