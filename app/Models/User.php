<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'national_id',
        'qiraat',
        'profile_image',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user's role name.
     */
    public function getRoleAttribute()
    {
        return $this->roles->first()?->name ?? 'student';
    }

    /**
     * Check if user is a student.
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is a sheikh.
     */
    public function isSheikh(): bool
    {
        return $this->hasRole('sheikh');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a supervisor.
     */
    public function isSupervisor(): bool
    {
        return $this->hasRole('supervisor');
    }

    /**
     * Get courses where user is enrolled as student.
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'student_id', 'course_id')
                    ->withPivot(['status', 'enrolled_at', 'approved_at', 'approved_by', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Get courses where user is assigned as sheikh.
     */
    public function teachingCourses()
    {
        return $this->belongsToMany(Course::class, 'course_sheikhs', 'sheikh_id', 'course_id')
                    ->withPivot(['group_id', 'role', 'assigned_at', 'assigned_by'])
                    ->withTimestamps();
    }

    /**
     * Get groups where user is assigned as student.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_students', 'student_id', 'group_id')
                    ->withPivot(['assigned_at', 'assigned_by'])
                    ->withTimestamps();
    }

    /**
     * Get groups where user is assigned as sheikh.
     */
    public function teachingGroups()
    {
        return $this->belongsToMany(Group::class, 'course_sheikhs', 'sheikh_id', 'group_id')
                    ->withPivot(['course_id', 'role', 'assigned_at', 'assigned_by'])
                    ->withTimestamps();
    }

    /**
     * Get hifz logs for this student.
     */
    public function hifzLogs()
    {
        return $this->hasMany(HifzLog::class, 'student_id');
    }

    /**
     * Get hifz logs created by this sheikh.
     */
    public function createdHifzLogs()
    {
        return $this->hasMany(HifzLog::class, 'sheikh_id');
    }

    /**
     * Get review logs for this student.
     */
    public function reviewLogs()
    {
        return $this->hasMany(ReviewLog::class, 'student_id');
    }

    /**
     * Get review logs created by this sheikh.
     */
    public function createdReviewLogs()
    {
        return $this->hasMany(ReviewLog::class, 'sheikh_id');
    }

    /**
     * Get notifications sent by this user.
     */
    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'created_by');
    }

    /**
     * Get notifications received by this user.
     */
    public function receivedNotifications()
    {
        return $this->belongsToMany(Notification::class, 'user_notifications')
                    ->withPivot(['is_read', 'read_at'])
                    ->withTimestamps();
    }

    /**
     * Get courses created by this user (admin).
     */
    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    /**
     * Get user's initials for avatar.
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        if (count($names) >= 2) {
            return mb_substr($names[0], 0, 1) . mb_substr($names[1], 0, 1);
        }
        return mb_substr($this->name, 0, 1);
    }

    /**
     * Get user's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get user's profile image URL.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return null;
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by role.
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Get user's latest hifz log.
     */
    public function getLatestHifzLogAttribute()
    {
        return $this->hifzLogs()->latest()->first();
    }

    /**
     * Get user's latest review log.
     */
    public function getLatestReviewLogAttribute()
    {
        return $this->reviewLogs()->latest()->first();
    }

    /**
     * Get user's total memorized ayahs.
     */
    public function getTotalMemorizedAyahsAttribute(): int
    {
        return $this->hifzLogs()
                    ->selectRaw('SUM(end_ayah - start_ayah + 1) as total')
                    ->value('total') ?? 0;
    }

    /**
     * Get user's average evaluation score.
     */
    public function getAverageEvaluationAttribute(): float
    {
        $evaluationMap = [
            'excellent' => 5,
            'very_good' => 4,
            'good' => 3,
            'needs_improvement' => 2,
            'poor' => 1,
        ];

        $logs = $this->hifzLogs()->get();
        if ($logs->isEmpty()) {
            return 0;
        }

        $totalScore = $logs->sum(function ($log) use ($evaluationMap) {
            return $evaluationMap[$log->evaluation] ?? 0;
        });

        return round($totalScore / $logs->count(), 2);
    }
}

