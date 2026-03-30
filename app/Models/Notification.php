<?php
/**
 * UPDATED NOTIFICATION MODEL
 * 
 * File: app/Models/Notification.php
 * 
 * Updated to work with:
 * - Existing fields: title, message, data, created_by, timestamps
 * - New v3 fields: target, is_active, sent_at
 * - Type enum values: old (info, success, warning, error) + new (enrollment, course_start, course_end, custom_broadcast)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',           // Old: info, success, warning, error | New: enrollment, course_start, course_end, custom_broadcast
        'target',         // NEW: students, teachers, both
        'data',           // JSON with additional context
        'is_active',      // NEW: boolean for draft/active notifications
        'created_by',     // User who created it
        'sent_at',        // NEW: when notification was actually sent
    ];

    protected $casts = [
        'data' => 'json',
        'is_active' => 'boolean',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'target_label',
        'type_label',
    ];

    /**
     * Get the user who created this notification
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all users who received this notification
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_notifications')
                    ->withPivot(['is_read', 'read_at'])
                    ->withTimestamps();
    }

    /**
     * Get target label in Arabic
     */
    public function getTargetLabelAttribute(): string
    {
        // Default to 'both' if target is null (for backward compatibility with old records)
        $target = $this->target ?? 'both';
        
        return match($target) {
            'students' => 'الطلاب',
            'teachers' => 'المعلمون والمشايخ',
            'both' => 'الجميع',
            default => 'غير محدد',
        };
    }

    /**
     * Get type label in Arabic
     * Handles both old and new type values
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            // New v3 types
            'enrollment' => 'قبول الالتحاق',
            'course_start' => 'بداية الدورة',
            'course_end' => 'نهاية الدورة',
            'custom_broadcast' => 'إشعار مخصص',
            // Old types (for backward compatibility)
            'info' => 'معلومة',
            'success' => 'نجاح',
            'warning' => 'تحذير',
            'error' => 'خطأ',
            default => 'غير محدد',
        };
    }

    /**
     * Get course data if notification is course-related
     */
    public function getCourseAttribute()
    {
        if ($this->data && isset($this->data['course_id'])) {
            return Course::find($this->data['course_id']);
        }
        return null;
    }

    /**
     * Scope: only active notifications
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: only sent notifications
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope: unsent (drafted) notifications
     */
    public function scopeUnsent($query)
    {
        return $query->whereNull('sent_at');
    }

    /**
     * Scope: by target audience (includes 'both')
     */
    public function scopeForTarget($query, string $target)
    {
        return $query->where(function ($q) use ($target) {
            $q->where('target', $target)
              ->orWhere('target', 'both')
              ->orWhereNull('target'); // For backward compatibility
        });
    }

    /**
     * Scope: by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: recent notifications
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get unread count
     */
    public function getUnreadCountAttribute(): int
    {
        return $this->recipients()
                    ->wherePivot('is_read', false)
                    ->count();
    }

    /**
     * Check if notification was read by user
     */
    public function isReadBy(User $user): bool
    {
        return $this->recipients()
                    ->where('user_id', $user->id)
                    ->wherePivot('is_read', true)
                    ->exists();
    }

    /**
     * Check if notification was read by user
     */
    public function isUnreadBy(User $user): bool
    {
        return !$this->isReadBy($user);
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update(['sent_at' => now()]);
    }

    /**
     * Mark as active
     */
    public function markAsActive(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Mark as inactive
     */
    public function markAsInactive(): void
    {
        $this->update(['is_active' => false]);
    }
}