<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTwoRecord extends Model
{
    protected $table = 'form_two_records';

    protected $fillable = [
        'group_id',
        'month',
        'year',
        'day',
        'subject_id',
        'teacher_id',
        'total_hours',
        'hours_per_class',
        'status',
        'replacement_teacher_id',
        'bonus_hours',
        'used_hours',
        'absent_reason',
        'replacement_comment',
        'mode',
    ];

    public function group()
    {
        return $this->belongsTo(\App\Models\FirstCourseGroup::class, 'group_id');
    }

    public function subject()
    {
        return $this->belongsTo(\App\Models\FirstCourseSubject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\FristCourseTeacher::class, 'teacher_id');
    }

    public function replacementTeacher()
    {
        return $this->belongsTo(\App\Models\FristCourseTeacher::class, 'replacement_teacher_id');
    }

    public function isNormal(): bool
    {
        return $this->status === 'normal';
    }

    public function isSick(): bool
    {
        return $this->status === 'sick';
    }

    public function isReplacement(): bool
    {
        return $this->status === 'replacement';
    }
}
