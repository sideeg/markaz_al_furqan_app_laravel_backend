<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HifzLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'sheikh_id',
        'course_id',
        'date',
        'start_sura',
        'start_ayah',
        'end_sura',
        'end_ayah',
        'evaluation',
        'comment'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function sheikh()
    {
        return $this->belongsTo(User::class, 'sheikh_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}