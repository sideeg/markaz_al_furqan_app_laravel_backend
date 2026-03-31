<?php
/**
 * UPDATED NOTIFICATION SERVICE
 * 
 * File: app/Services/NotificationService.php
 * 
 * Updated to work with existing notifications table
 * Uses correct field names and handles backward compatibility
 */

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send notification to students only
     */
    public function notifyStudents(
        string $title, 
        string $message, 
        string $type = 'custom_broadcast',
        ?array $data = null,
        ?int $createdBy = null
    ): Notification {
        return $this->createAndSendNotification(
            title: $title,
            message: $message,
            type: $type,
            target: 'students',
            data: $data,
            createdBy: $createdBy
        );
    }

    /**
     * Send notification to teachers/sheikhs only
     */
    public function notifyTeachers(
        string $title, 
        string $message, 
        string $type = 'custom_broadcast',
        ?array $data = null,
        ?int $createdBy = null
    ): Notification {
        return $this->createAndSendNotification(
            title: $title,
            message: $message,
            type: $type,
            target: 'teachers',
            data: $data,
            createdBy: $createdBy
        );
    }

    /**
     * Send notification to both students and teachers
     */
    public function notifyAll(
        string $title, 
        string $message, 
        string $type = 'custom_broadcast',
        ?array $data = null,
        ?int $createdBy = null
    ): Notification {
        return $this->createAndSendNotification(
            title: $title,
            message: $message,
            type: $type,
            target: 'both',
            data: $data,
            createdBy: $createdBy
        );
    }

    /**
     * Send enrollment approval notification to a specific student
     */
    public function notifyEnrollmentApproved(
        int $studentId,
        int $courseId,
        string $courseName
    ): Notification {
        $notification = Notification::create([
            'title' => 'تم قبول التحاقك',
            'message' => "تم قبول التحاقك في دورة: {$courseName}",
            'type' => 'enrollment',
            'target' => 'students',
            'data' => ['course_id' => $courseId, 'student_id' => $studentId],
            'is_active' => true,
            'created_by' => auth()->id() ?? 1,
            'sent_at' => now(),
        ]);

        // Send only to this specific student
        $notification->recipients()->sync([$studentId]);

        return $notification;
    }

    /**
     * Send course start notification
     */
    public function notifyCourseStarting(
        int $courseId,
        string $courseName,
        array $recipientIds = []
    ): Notification {
        $notification = Notification::create([
            'title' => 'الدورة ستبدأ قريباً',
            'message' => "دورة {$courseName} ستبدأ في الوقت المحدد",
            'type' => 'course_start',
            'target' => 'both',
            'data' => ['course_id' => $courseId],
            'is_active' => true,
            'created_by' => auth()->id() ?? 1,
            'sent_at' => now(),
        ]);

        if (empty($recipientIds)) {
            $recipientIds = $this->getRecipientIds('both');
        }

        $notification->recipients()->sync($recipientIds);

        return $notification;
    }

    /**
     * Send course end notification
     */
    public function notifyCourseEnding(
        int $courseId,
        string $courseName,
        array $recipientIds = []
    ): Notification {
        $notification = Notification::create([
            'title' => 'الدورة انتهت',
            'message' => "انتهت دورة {$courseName}",
            'type' => 'course_end',
            'target' => 'both',
            'data' => ['course_id' => $courseId],
            'is_active' => true,
            'created_by' => auth()->id() ?? 1,
            'sent_at' => now(),
        ]);

        if (empty($recipientIds)) {
            $recipientIds = $this->getRecipientIds('both');
        }

        $notification->recipients()->sync($recipientIds);

        return $notification;
    }

    /**
     * Core method: Create and send notification
     */
    private function createAndSendNotification(
        string $title,
        string $message,
        string $type,
        string $target,
        ?array $data = null,
        ?int $createdBy = null
    ): Notification {
        // Create notification
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target' => $target,
            'data' => $data,
            'is_active' => true,
            'created_by' => $createdBy ?? auth()->id() ?? 1,
            'sent_at' => now(),
        ]);

        // Send to recipients based on target
        $recipientIds = $this->getRecipientIds($target);
        $notification->recipients()->sync($recipientIds);

        return $notification;
    }

    /**
     * Get recipient IDs based on target
     */
    private function getRecipientIds(string $target): array
    {
        return match($target) {
            'students' => $this->getStudentIds(),
            'teachers' => $this->getTeacherIds(),
            'both' => array_unique(array_merge($this->getStudentIds(), $this->getTeacherIds())),
            default => [],
        };
    }

    /**
     * Get all student IDs (users with 'student' role)
     * Using Spatie Laravel Permissions
     */
    private function getStudentIds(): array
    {
        return User::role('student')
                   ->where('is_active', true)
                   ->pluck('id')
                   ->toArray();
    }

    /**
     * Get all teacher/sheikh IDs (users with 'sheikh', 'teacher', 'admin' roles)
     * Using Spatie Laravel Permissions
     */
    private function getTeacherIds(): array
    {
        return User::role(['sheikh', 'teacher', 'admin', 'supervisor'])
                   ->where('is_active', true)
                   ->pluck('id')
                   ->toArray();
    }

    /**
     * Get count of unread notifications for a user
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
                    ->wherePivot('is_read', false)
                    ->count();
    }

    /**
     * Mark notification as read for a user
     */
    public function markAsRead(User $user, int $notificationId): bool
    {
        return (bool) $user->notifications()
                          ->wherePivot('notification_id', $notificationId)
                          ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()
             ->wherePivot('is_read', false)
             ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Get paginated notifications for a user
     */
    public function getPaginatedNotifications(User $user, int $perPage = 15)
    {
        return $user->notifications()
                    ->latest('user_notifications.created_at')
                    ->paginate($perPage);
    }

    /**
     * Delete notification for a user (from their notification list)
     */
    public function deleteForUser(User $user, int $notificationId): bool
    {
        return (bool) $user->notifications()
                          ->detach($notificationId);
    }

    /**
     * Create a draft notification (not sent yet)
     */
    public function createDraft(
        string $title,
        string $message,
        string $type,
        string $target,
        ?array $data = null
    ): Notification {
        return Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target' => $target,
            'data' => $data,
            'is_active' => false,  // Draft (not active)
            'created_by' => auth()->id() ?? 1,
            'sent_at' => null,     // Not sent yet
        ]);
    }

    /**
     * Publish a draft notification
     */
    public function publishDraft(int $notificationId, array $recipientIds = []): Notification
    {
        $notification = Notification::findOrFail($notificationId);

        if (empty($recipientIds)) {
            $recipientIds = $this->getRecipientIds($notification->target);
        }

        $notification->update([
            'is_active' => true,
            'sent_at' => now(),
        ]);

        $notification->recipients()->sync($recipientIds);

        return $notification;
    }
}