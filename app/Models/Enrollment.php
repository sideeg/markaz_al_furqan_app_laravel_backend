<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'course_enrollments';

    protected $fillable = [
        'course_id',
        'student_id',
        'status',
        'enrolled_at',
        'approved_at',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
