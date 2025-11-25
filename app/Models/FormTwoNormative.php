<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTwoNormative extends Model
{
    protected $table = 'form_two_normatives';

    protected $fillable = [
        'group_id',
        'subject_id',
        'teacher_id',
        'month',
        'year',
        'total_hours',
        'hours_per_class',
    ];

    public function group()
    {
        return $this->belongsTo(FirstCourseGroup::class, 'group_id');
    }

    public function subject()
    {
        return $this->belongsTo(FirstCourseSubject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(FristCourseTeacher::class, 'teacher_id');
    }
}
