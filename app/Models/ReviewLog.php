<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'sheikh_id',
        'date',
        'surah',
        'start_ayah',
        'end_ayah',
        'evaluation',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function sheikh()
    {
        return $this->belongsTo(User::class, 'sheikh_id');
    }
}
