<?php
/**
 * ADMIN ACTIVITY LOG MODEL
 * 
 * File: app/Models/AdminActivityLog.php
 * 
 * Tracks all admin actions for audit trail
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'model_type',
        'model_id',
        'model_name',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_data' => 'json',
        'new_data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'action_label',
        'time_ago',
    ];

    /**
     * Get the admin who performed the action
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get human-readable action label in Arabic
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'create' => 'إنشاء',
            'update' => 'تحديث',
            'delete' => 'حذف',
            'send_notification' => 'إرسال إشعار',
            'approve_enrollment' => 'قبول الالتحاق',
            'reject_enrollment' => 'رفض الالتحاق',
            'assign_sheikh' => 'تعيين شيخ',
            default => $this->action,
        };
    }

    /**
     * Get model type label in Arabic
     */
    public function getModelTypeLabelAttribute(): string
    {
        return match($this->model_type) {
            'Course' => 'دورة',
            'Mosque' => 'مسجد',
            'Group' => 'مجموعة',
            'Notification' => 'إشعار',
            'CourseEnrollment' => 'التحاق',
            'CourseSheikh' => 'شيخ الدورة',
            default => $this->model_type,
        };
    }

    /**
     * Get time ago string (e.g., "2 hours ago")
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get full activity description with admin name
     */
    public function getFullDescriptionAttribute(): string
    {
        return "{$this->admin?->name} - {$this->description}";
    }

    /**
     * Scope: Filter by admin
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope: Filter by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by model type
     */
    public function scopeByModelType($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Only recent logs (last 30 days)
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Only course-related activities
     */
    public function scopeCourseActivities($query)
    {
        return $query->where('model_type', 'Course');
    }

    /**
     * Scope: Only notification activities
     */
    public function scopeNotificationActivities($query)
    {
        return $query->where('action', 'send_notification');
    }

    /**
     * Scope: Only important actions (creates, deletes, approvals)
     */
    public function scopeImportantActions($query)
    {
        return $query->whereIn('action', ['create', 'delete', 'approve_enrollment', 'reject_enrollment']);
    }

    /**
     * Scope: Order by newest first
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get changes made in this activity (for updates)
     */
    public function getChanges(): array
    {
        if (!$this->old_data || !$this->new_data) {
            return [];
        }

        $changes = [];
        foreach ($this->new_data as $key => $newValue) {
            $oldValue = $this->old_data[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Check if this was a critical action
     */
    public function isCriticalAction(): bool
    {
        return in_array($this->action, [
            'create',
            'delete',
            'approve_enrollment',
            'reject_enrollment',
        ]);
    }
}