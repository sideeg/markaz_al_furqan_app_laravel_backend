<?php
/**
 * ACTIVITY LOGGER SERVICE
 * 
 * File: app/Services/ActivityLogger.php
 * 
 * Handles all activity logging logic used by observers
 * Provides centralized logging to maintain consistency
 */

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a model creation
     */
    public static function logCreate(Model $model, ?array $data = null): AdminActivityLog
    {
        return self::log(
            action: 'create',
            model: $model,
            description: self::getCreateDescription($model),
            newData: $data ?? $model->toArray(),
        );
    }

    /**
     * Log a model update
     */
    public static function logUpdate(Model $model, array $changes): AdminActivityLog
    {
        return self::log(
            action: 'update',
            model: $model,
            description: self::getUpdateDescription($model, $changes),
            oldData: self::extractOldValues($model, $changes),
            newData: self::extractNewValues($model, $changes),
        );
    }

    /**
     * Log a model deletion
     */
    public static function logDelete(Model $model): AdminActivityLog
    {
        return self::log(
            action: 'delete',
            model: $model,
            description: self::getDeleteDescription($model),
            oldData: $model->toArray(),
        );
    }

    /**
     * Log notification sent
     */
    public static function logNotificationSent($notification): AdminActivityLog
    {
        return self::log(
            action: 'send_notification',
            modelType: 'Notification',
            modelId: $notification->id ?? 0,
            modelName: $notification->title ?? 'Notification',
            description: "أرسل إشعار: {$notification->title}",
            newData: [
                'title' => $notification->title,
                'target' => $notification->target ?? 'all',
                'recipients_count' => $notification->recipients()->count() ?? 0,
            ],
        );
    }

    /**
     * Log enrollment approval
     */
    public static function logEnrollmentApproved($enrollment, $course, $student): AdminActivityLog
    {
        return self::log(
            action: 'approve_enrollment',
            modelType: 'CourseEnrollment',
            modelId: $enrollment->id,
            modelName: "التحاق {$student?->name} في {$course?->name}",
            description: "قبل التحاق الطالب {$student?->name} في دورة {$course?->name}",
            newData: ['status' => 'approved'],
        );
    }

    /**
     * Log enrollment rejection
     */
    public static function logEnrollmentRejected($enrollment, $course, $student): AdminActivityLog
    {
        return self::log(
            action: 'reject_enrollment',
            modelType: 'CourseEnrollment',
            modelId: $enrollment->id,
            modelName: "رفض التحاق {$student?->name} في {$course?->name}",
            description: "رفض التحاق الطالب {$student?->name} في دورة {$course?->name}",
            newData: ['status' => 'rejected'],
        );
    }

    /**
     * Log sheikh assignment
     */
    public static function logSheikhAssigned($sheikh, $course): AdminActivityLog
    {
        return self::log(
            action: 'assign_sheikh',
            modelType: 'CourseSheikh',
            modelId: $course->id,
            modelName: "تعيين الشيخ {$sheikh?->name} في {$course?->name}",
            description: "عيّن الشيخ {$sheikh?->name} في دورة {$course?->name}",
            newData: [
                'sheikh_id' => $sheikh->id,
                'course_id' => $course->id,
                'sheikh_name' => $sheikh->name,
                'course_name' => $course->name,
            ],
        );
    }

    /**
     * Core logging method
     */
    private static function log(
        string $action,
        ?Model $model = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?string $modelName = null,
        ?string $description = null,
        ?array $oldData = null,
        ?array $newData = null,
    ): AdminActivityLog {
        // Get model info if model is provided
        if ($model) {
            $modelType = $modelType ?? class_basename($model);
            $modelId = $modelId ?? $model->id;
            $modelName = $modelName ?? self::getModelDisplayName($model);
        }

        // Validate we have model info
        if (!$modelType || !$modelId || !$modelName) {
            throw new \Exception('Missing required model information for activity logging');
        }

        // Create the activity log
        return AdminActivityLog::create([
            'admin_id' => Auth::id() ?? 1,  // Fallback to admin user if not authenticated
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'model_name' => $modelName,
            'description' => $description ?? self::generateDescription($action, $modelType, $modelName),
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }

    /**
     * Get model display name (arabic-friendly)
     */
    private static function getModelDisplayName(Model $model): string
    {
        // Try to use 'name' field first
        if ($model->getAttribute('name')) {
            return $model->getAttribute('name');
        }

        // Try to use 'title' field
        if ($model->getAttribute('title')) {
            return $model->getAttribute('title');
        }

        // Fallback to model class name with ID
        return class_basename($model) . ' #' . $model->id;
    }

    /**
     * Get create description
     */
    private static function getCreateDescription(Model $model): string
    {
        $type = match(class_basename($model)) {
            'Course' => 'دورة',
            'Mosque' => 'مسجد',
            'Group' => 'مجموعة',
            default => class_basename($model),
        };

        return "أنشأ {$type} جديد: " . ($model->name ?? $model->title);
    }

    /**
     * Get update description
     */
    private static function getUpdateDescription(Model $model, array $changes): string
    {
        $type = match(class_basename($model)) {
            'Course' => 'دورة',
            'Mosque' => 'مسجد',
            'Group' => 'مجموعة',
            default => class_basename($model),
        };

        $fields = implode(', ', array_keys($changes));
        $identifier = $model->name ?? $model->title;

        return "حدّث {$type} {$identifier}: {$fields}";
    }

    /**
     * Get delete description
     */
    private static function getDeleteDescription(Model $model): string
    {
        $type = match(class_basename($model)) {
            'Course' => 'دورة',
            'Mosque' => 'مسجد',
            'Group' => 'مجموعة',
            default => class_basename($model),
        };
        $identifier = $model->name ?? $model->title;
        return "حذف {$type}: {$identifier}";
    }

    /**
     * Generate default description
     */
    private static function generateDescription(string $action, string $modelType, string $modelName): string
    {
        $actionLabel = match($action) {
            'create' => 'أنشأ',
            'update' => 'حدّث',
            'delete' => 'حذف',
            'send_notification' => 'أرسل إشعار',
            'approve_enrollment' => 'قبل',
            'reject_enrollment' => 'رفض',
            'assign_sheikh' => 'عيّن',
            default => $action,
        };

        return "{$actionLabel} {$modelType}: {$modelName}";
    }

    /**
     * Extract old values from model for comparison
     */
    private static function extractOldValues(Model $model, array $changes): array
    {
        $oldData = [];
        foreach (array_keys($changes) as $field) {
            $oldData[$field] = $model->getOriginal($field);
        }
        return $oldData;
    }

    /**
     * Extract new values from model
     */
    private static function extractNewValues(Model $model, array $changes): array
    {
        $newData = [];
        foreach (array_keys($changes) as $field) {
            $newData[$field] = $model->getAttribute($field);
        }
        return $newData;
    }
}