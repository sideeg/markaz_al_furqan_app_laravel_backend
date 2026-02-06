<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name', 'description', 'course_id', 'max_students','sheikh_id', 'current_students', 'is_active', 'schedule_details', 'created_by',
    ];

    public function sheikh()
    {
        return $this->belongsTo(User::class, 'sheikh_id');
    }
    
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'group_students', 'group_id', 'student_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
