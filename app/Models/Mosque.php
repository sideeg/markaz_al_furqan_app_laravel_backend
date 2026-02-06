<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mosque extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'phone',
        'email',
        'latitude',
        'longitude',
        'image_path',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * المستخدم الذي أنشأ المسجد
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * الدورات المرتبطة بهذا المسجد
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
