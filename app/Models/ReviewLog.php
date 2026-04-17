<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewLog extends Model
{
    use HasFactory, SoftDeletes; 

   

    protected $fillable = [
        'student_id',
        'sheikh_id',
        'course_id',
        'group_id',
        'session_date',
        'session_time',
        'start_surah',
        'end_surah',
        'start_ayah',
        'end_ayah',
        'evaluation',
        'notes',
    ];
        protected $casts = [
        'session_date' => 'date',
        'session_time' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function sheikh()
    {
        return $this->belongsTo(User::class, 'sheikh_id');
    }
    /**
     * Get the course
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
 
    /**
     * Get the group (if applicable)
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
 
   
}
