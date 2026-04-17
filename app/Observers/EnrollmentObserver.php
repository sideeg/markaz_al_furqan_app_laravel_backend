<?php
namespace App\Observers;
// ═══════════════════════════════════════════════════════════════════
// Path: app/Observers/EnrollmentObserver.php
// 

use App\Models\Enrollment;
use App\Services\NotificationService;
use App\Services\ActivityLogger;

class EnrollmentObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Trigger: Enrollment status changed (approved/rejected)
     */
    public function updated(Enrollment $enrollment): void
    {
        // ✅ Only trigger if status actually changed
        if (!$enrollment->wasChanged('status')) {
            return;
        }

        $course = $enrollment->course;
        $student = $enrollment->student;

        // ✅ Course might be soft-deleted, handle gracefully
        if (!$course || !$student) {
            return;
        }

        // ─────────────────────────────────────────────────────────────
        // APPROVED
        // ─────────────────────────────────────────────────────────────
        if ($enrollment->status === 'approved') {
            // 1. Log activity for admin dashboard
            ActivityLogger::logEnrollmentApproved($enrollment, $course, $student);

            // 2. Send push notification to student
            // This:
            // - Creates DB record
            // - Dispatches FCM job
            // - Targets specific user (not topic)
            $this->notificationService->notifyEnrollmentApproved(
                $student->id,
                $course->id,
                $course->name
            );
        }

        // ─────────────────────────────────────────────────────────────
        // REJECTED
        // ─────────────────────────────────────────────────────────────
        elseif ($enrollment->status === 'rejected') {
            // 1. Log activity
            ActivityLogger::logEnrollmentRejected($enrollment, $course, $student);

            // 2. ✅ NEW: Send notification to student about rejection
            // Reason can be extracted from enrollment if available
            $reason = $enrollment->rejection_reason ?? null;

            $this->notificationService->notifyEnrollmentRejected(
                $student->id,
                $course->id,
                $course->name,
                $reason
            );
        }

        // ─────────────────────────────────────────────────────────────
        // PENDING (no notification, just ack)
        // ─────────────────────────────────────────────────────────────
        // If reverted to pending, we could notify but optional
    }
}