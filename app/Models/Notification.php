<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'target',
        'created_by'
    ];

    public function getTargetLabelAttribute()
    {
        return match($this->target) {
            'students' => 'الطلاب',
            'sheikhs' => 'المشايخ',
            'all' => 'الجميع',
            default => 'غير معروف',
        };
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}