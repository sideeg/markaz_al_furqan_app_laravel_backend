<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 

class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'mosque_id',
        'image_path',
        'start_date',
        'end_date',
        'max_students',
        'current_students',
        'is_active',
        'is_registration_open',
        'requirements',
        'schedule_details',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_registration_open' => 'boolean',
        'max_students' => 'integer',
        'current_students' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'type_display_name',
        'image_url',
        'enrollment_percentage',
        'available_slots',
        'can_enroll',
    ];

    /**
     * Course types.
     */
    const TYPE_ONLINE = 'online';
    const TYPE_OPEN = 'open';
    const TYPE_CLOSED = 'closed';

    /**
     * Get the mosque that owns the course.
     */
    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    /**
     * Get the user who created the course.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get enrolled students.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'student_id')
                    ->withPivot(['status', 'enrolled_at', 'approved_at', 'approved_by', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Get approved students only.
     */
    public function approvedStudents()
    {
        return $this->students()->wherePivot('status', 'approved');
    }

    /**
     * Get pending students.
     */
    public function pendingStudents()
    {
        return $this->students()->wherePivot('status', 'pending');
    }

    /**
     * Get assigned sheikhs.
     */
    public function sheikhs()
    {
        return $this->belongsToMany(User::class, 'course_sheikhs', 'course_id', 'sheikh_id')
                    ->withPivot(['group_id', 'role', 'assigned_at', 'assigned_by'])
                    ->withTimestamps();
    }

    /**
     * Get course groups.
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get course enrollments.
     */
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get hifz logs for this course.
     */
    public function hifzLogs()
    {
        return $this->hasMany(HifzLog::class);
    }

    /**
     * Get review logs for this course.
     */
    public function reviewLogs()
    {
        return $this->hasMany(ReviewLog::class);
    }

    /**
     * Get course type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_ONLINE => 'عبر الإنترنت',
            self::TYPE_OPEN => 'مفتوحة',
            self::TYPE_CLOSED => 'مغلقة',
            default => $this->type,
        };
    }

    /**
     * Get course image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    /**
     * Get enrollment percentage.
     */
    public function getEnrollmentPercentageAttribute(): float
    {
        if ($this->max_students == 0) {
            return 0;
        }
        return round(($this->current_students / $this->max_students) * 100, 2);
    }

    /**
     * Get available slots.
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->max_students - $this->current_students);
    }

    /**
     * Check if course can accept enrollments.
     */
    public function getCanEnrollAttribute(): bool
    {
        return $this->is_active && 
               $this->is_registration_open && 
               $this->current_students < $this->max_students;
    }

    /**
     * Check if course is online.
     */
    public function isOnline(): bool
    {
        return $this->type === self::TYPE_ONLINE;
    }

    /**
     * Check if course is open.
     */
    public function isOpen(): bool
    {
        return $this->type === self::TYPE_OPEN;
    }

    /**
     * Check if course is closed.
     */
    public function isClosed(): bool
    {
        return $this->type === self::TYPE_CLOSED;
    }

    /**
     * Scope to filter active courses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter courses with open registration.
     */
    public function scopeOpenRegistration($query)
    {
        return $query->where('is_registration_open', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to search courses.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('mosque', function ($mq) use ($search) {
                  $mq->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope to filter by mosque.
     */
    public function scopeByMosque($query, int $mosqueId)
    {
        return $query->where('mosque_id', $mosqueId);
    }

    /**
     * Scope to get courses with available slots.
     */
    public function scopeWithAvailableSlots($query)
    {
        return $query->whereRaw('current_students < max_students');
    }

    /**
     * Increment current students count.
     */
    public function incrementStudentsCount(): void
    {
        $this->increment('current_students');
    }

    /**
     * Decrement current students count.
     */
    public function decrementStudentsCount(): void
    {
        $this->decrement('current_students');
    }

    /**
     * Toggle registration status.
     */
    public function toggleRegistration(): void
    {
        $this->update(['is_registration_open' => !$this->is_registration_open]);
    }

    /**
     * Get course statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_students' => $this->current_students,
            'approved_students' => $this->approvedStudents()->count(),
            'pending_students' => $this->pendingStudents()->count(),
            'total_groups' => $this->groups()->count(),
            'total_sheikhs' => $this->sheikhs()->count(),
            'total_hifz_logs' => $this->hifzLogs()->count(),
            'total_review_logs' => $this->reviewLogs()->count(),
            'enrollment_percentage' => $this->enrollment_percentage,
            'available_slots' => $this->available_slots,
        ];
    }

    /**
     * Check if user is enrolled in this course.
     */
    public function isUserEnrolled(int $userId): bool
    {
        return $this->students()->where('student_id', $userId)->exists();
    }

    /**
     * Get user's enrollment status.
     */
    public function getUserEnrollmentStatus(int $userId): ?string
    {
        $enrollment = $this->students()->where('student_id', $userId)->first();
        return $enrollment?->pivot->status;
    }

    /**
     * Enroll a user in the course.
     */
    public function enrollUser(int $userId, string $notes = null): CourseEnrollment
    {
        return CourseEnrollment::create([
            'course_id' => $this->id,
            'student_id' => $userId,
            'status' => 'pending',
            'notes' => $notes,
        ]);
    }

    /**
     * Approve user enrollment.
     */
    public function approveEnrollment(int $userId, int $approvedBy): bool
    {
        $enrollment = $this->enrollments()
                          ->where('student_id', $userId)
                          ->where('status', 'pending')
                          ->first();

        if ($enrollment) {
            $enrollment->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $approvedBy,
            ]);
            
            $this->incrementStudentsCount();
            return true;
        }

        return false;
    }

    /**
     * Reject user enrollment.
     */
    public function rejectEnrollment(int $userId): bool
    {
        $enrollment = $this->enrollments()
                          ->where('student_id', $userId)
                          ->where('status', 'pending')
                          ->first();

        if ($enrollment) {
            $enrollment->update(['status' => 'rejected']);
            return true;
        }

        return false;
    }
}

