<?php
// Path: app/Services/NotificationService.php

namespace App\Services;

use App\Jobs\PushFcmNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Course;

class NotificationService
{
    private function getTopicTargetsForUser(User $user): array
    {
        $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
        Log::info("FCM topic targets — userId: {$user->id}, roles: " . implode(', ', $roles));

        if (array_intersect($roles, ['admin', 'super-admin', 'superadmin'])) {
            return ['teachers', 'students', 'both'];
        }
        if (array_intersect($roles, ['teacher', 'sheikh', 'شيخ', 'معلم'])) {
            return ['teachers', 'both'];
        }
        if (array_intersect($roles, ['student', 'طالب'])) {
            return ['students', 'both'];
        }
        Log::warning("No role matched for userId: {$user->id} — fallback to ['both']");
        return ['both'];
    }

    /**
     * Core method: Create notification record AND dispatch FCM job.
     *
     * KEY FIX: $data always gets 'type' merged in so the FCM payload
     * contains data['type'] and Flutter can route notification taps correctly.
     */
    public function createAndSendNotification(
        int $userId,
        string $title,
        string $message,
        string $type,
        string $target,
        ?array $data = null
    ): Notification {
        try {
            $validTargets = ['individual', 'students', 'teachers', 'both'];
            if (!in_array($target, $validTargets) && !is_numeric($target)) {
                throw new \Exception("Invalid target: $target");
            }

            $notification = Notification::create([
                'created_by' => $userId,
                'title'      => $title,
                'message'    => $message,
                'type'       => $type,
                'target'     => is_numeric($target) ? 'individual' : $target,
                'data'       => $data,
                'sent_at'    => now(),
            ]);

            // FIX: Always include 'type' in the FCM data payload.
            // Without this, Flutter receives data['type'] == null and
            // cannot route the notification tap to the correct screen.
            $fcmData = array_merge(['type' => $type], $data ?? []);

            if (in_array($target, ['students', 'teachers', 'both'])) {
                PushFcmNotification::dispatch($target, $title, $message, $fcmData);
            } elseif (is_numeric($target)) {
                $notification->recipients()->attach((int)$target, [
                    'is_read' => false,
                    'read_at' => null,
                ]);
                PushFcmNotification::dispatch([(int)$target], $title, $message, $fcmData);
            }

            return $notification;
        } catch (\Exception $e) {
            Log::error('NotificationService Error: ' . $e->getMessage());
            throw $e;
        }
    }

    // ── Retrieve ──────────────────────────────────────────────────────────────

    public function getUserNotifications(int $userId, $perPage = 15)
    {
        $user         = User::findOrFail($userId);
        $topicTargets = $this->getTopicTargetsForUser($user);

        return Notification::where(function ($query) use ($userId) {
                $query->whereHas('recipients', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->orWhere(function ($query) use ($topicTargets) {
                $query->whereIn('target', $topicTargets);
            })
            ->with(['recipients' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);
    }

    public function getUnreadCount(int $userId): int
    {
        $user         = User::findOrFail($userId);
        $topicTargets = $this->getTopicTargetsForUser($user);

        $directUnread = Notification::whereHas('recipients', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('is_read', false);
            })->count();

        $topicUnread = Notification::whereIn('target', $topicTargets)
            ->whereDoesntHave('recipients', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count();

        Log::info("getUnreadCount — userId: {$userId}, targets: [" . implode(',', $topicTargets) . "], direct: {$directUnread}, topic: {$topicUnread}");

        return $directUnread + $topicUnread;
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::findOrFail($notificationId);
        $exists = $notification->recipients()->where('user_id', $userId)->exists();

        if ($exists) {
            $notification->recipients()->updateExistingPivot($userId, [
                'is_read' => true, 'read_at' => now(),
            ]);
        } else {
            $notification->recipients()->attach($userId, [
                'is_read' => true, 'read_at' => now(),
            ]);
        }
        return true;
    }

    public function markAllAsRead(int $userId): bool
    {
        $user         = User::findOrFail($userId);
        $topicTargets = $this->getTopicTargetsForUser($user);

        DB::table('user_notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now(), 'updated_at' => now()]);

        $unreadTopicIds = Notification::whereIn('target', $topicTargets)
            ->whereDoesntHave('recipients', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->pluck('id');

        if ($unreadTopicIds->isNotEmpty()) {
            $now     = now();
            $inserts = $unreadTopicIds->map(fn($id) => [
                'user_id'         => $userId,
                'notification_id' => $id,
                'is_read'         => true,
                'read_at'         => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ])->toArray();
            DB::table('user_notifications')->insert($inserts);
        }

        Log::info("markAllAsRead — userId: {$userId}, topic pivot records: " . $unreadTopicIds->count());
        return true;
    }

    public function getAdminNotifications($perPage = 20)
    {
        return Notification::latest()->paginate($perPage);
    }

    // ── Broadcasts ────────────────────────────────────────────────────────────

    public function broadcastToStudents(string $title, string $message, ?array $data = null): Notification
    {
        return $this->createAndSendNotification(
            auth()->id(), $title, $message, 'custom_broadcast', 'students', $data
        );
    }

    public function broadcastToTeachers(string $title, string $message, ?array $data = null): Notification
    {
        return $this->createAndSendNotification(
            auth()->id(), $title, $message, 'custom_broadcast', 'teachers', $data
        );
    }

    public function broadcastToAll(string $title, string $message, ?array $data = null): Notification
    {
        return $this->createAndSendNotification(
            auth()->id(), $title, $message, 'custom_broadcast', 'both', $data
        );
    }

    // ── Enrollment ────────────────────────────────────────────────────────────

    public function notifyEnrollmentApproved(int $studentId, int $courseId, string $courseName): Notification
    {
        return $this->createAndSendNotification(
            1, 'تم قبولك في الدورة',
            "تم قبول انضمامك إلى دورة: {$courseName}",
            'enrollment', (string)$studentId,
            ['course_id' => $courseId, 'status' => 'approved']
            // 'type' => 'enrollment' is auto-merged by createAndSendNotification
        );
    }

    public function notifyEnrollmentRejected(int $studentId, int $courseId, string $courseName, ?string $reason = null): Notification
    {
        return $this->createAndSendNotification(
            1, 'تم رفض طلبك',
            "تم رفض انضمامك إلى دورة: {$courseName}" . ($reason ? "\nالسبب: $reason" : ""),
            'enrollment', (string)$studentId,
            ['course_id' => $courseId, 'status' => 'rejected']
        );
    }

    // ── Sheikh notifications ──────────────────────────────────────────────────

    public function notifySheikhNewStudent(int $sheikhId, string $studentName, string $groupName, int $groupId): Notification
    {
        // FCM data will contain: type=new_student, group_id, group_name, student_name
        // Flutter NotificationHelper routes 'new_student' → /students ✅
        return $this->createAndSendNotification(
            1, 'طالب جديد في حلقتك',
            "تمت إضافة الطالب {$studentName} إلى مجموعة {$groupName}",
            'new_student', (string)$sheikhId,
            ['group_id' => $groupId, 'group_name' => $groupName, 'student_name' => $studentName]
        );
    }

    public function notifySheikhStudentWithdrawn(int $sheikhId, string $studentName, string $courseName, int $courseId): Notification
    {
        // FCM data will contain: type=enrollment → Flutter routes to /students ✅
        return $this->createAndSendNotification(
            1, 'طالب انسحب من الدورة',
            "انسحب الطالب {$studentName} من دورة {$courseName}",
            'enrollment', (string)$sheikhId,
            ['course_id' => $courseId, 'course_name' => $courseName, 'student_name' => $studentName]
        );
    }

    public function notifySheikhStudentRemovedFromGroup(int $sheikhId, string $studentName, string $groupName, int $groupId): Notification
    {
        // FCM data will contain: type=enrollment → Flutter routes to /students ✅
        return $this->createAndSendNotification(
            1, 'طالب تمت إزالته من المجموعة',
            "تمت إزالة الطالب {$studentName} من مجموعة {$groupName}",
            'enrollment', (string)$sheikhId,
            ['group_id' => $groupId, 'group_name' => $groupName, 'student_name' => $studentName]
        );
    }

    // ── Course status ─────────────────────────────────────────────────────────

    public function notifyCourseStarting(int $courseId, string $courseName, string $startTime): Notification
    {
        return $this->createAndSendNotification(
            1, 'الدورة تبدأ اليوم',
            "دورة {$courseName} تبدأ اليوم الساعة {$startTime}",
            'course_start', 'students',
            ['course_id' => $courseId, 'course_name' => $courseName, 'start_time' => $startTime]
        );
    }

    public function notifyNewCourse(int $courseId, string $courseName): Notification
    {
        return $this->createAndSendNotification(
            1, ' دورة جديدة',
            "بشري سعيدة دورة {$courseName}الان تم الاعلان عنها ومتاحه للتسجيل",
            'custom_broadcast', 'both',
            ['course_id' => $courseId, 'course_name' => $courseName]
        );
    }

    /**
 * Notify students and sheikhs that a course has been completed.
 */
public function notifyCourseCompleted(Course $course)
{
    // 1. جلب معرفات الطلاب المقبولين فقط
    $studentIds = $course->approvedStudents()->pluck('users.id')->toArray();

    // 2. جلب معرفات المشايخ المرتبطين بالمجموعات في هذه الدورة
    $sheikhIds = $course->groups()
        ->whereNotNull('sheikh_id')
        ->pluck('sheikh_id')
        ->unique()
        ->toArray();

    DB::transaction(function () use ($course, $studentIds, $sheikhIds) {
        $type = 'course_end';
        $createdBy = auth()->id() ?? 1;
        $commonData = [
            'course_id' => (string) $course->id,
            'course_name' => $course->name,
        ];

        // --- أولاً: إرسال إشعارات الطلاب ---
        if (!empty($studentIds)) {
            $studentTitle = 'انتهاء الدورة';
            $studentMessage = "تم بحمد الله الانتهاء من دورة: {$course->name}. نسأل الله أن ينفعكم بما تعلمتم.";

            $this->sendNotificationProcess(
                $studentIds, 
                $studentTitle, 
                $studentMessage, 
                $type, 
                $commonData, 
                $createdBy
            );
        }

        // --- ثانياً: إرسال إشعارات المشايخ ---
        if (!empty($sheikhIds)) {
            $sheikhTitle = 'إتمام مهمة تعليمية';
            $sheikhMessage = "تم بحمد الله الانتهاء من دورة: {$course->name}. تقبل الله جهدكم وجعلكم من أهل القرآن الذين هم أهله وخاصته.";

            $this->sendNotificationProcess(
                $sheikhIds, 
                $sheikhTitle, 
                $sheikhMessage, 
                $type, 
                $commonData, 
                $createdBy
            );
        }
    });
}

/**
 * وظيفة مساعدة لإتمام عملية التخزين والإرسال (لتجنب تكرار الكود)
 */
private function sendNotificationProcess($userIds, $title, $message, $type, $data, $createdBy)
{
    // إنشاء سجل الإشعار في قاعدة البيانات
    $notification = Notification::create([
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'target' => 'individual',
        'data' => $data,
        'created_by' => $createdBy,
        'sent_at' => now(),
    ]);

    // ربط المستخدمين بالإشعار (للعدّاد وللقراءة)
    $notification->recipients()->attach(
        collect($userIds)->mapWithKeys(function ($id) {
            return [$id => ['is_read' => false]];
        })->toArray()
    );

    // إرسال عبر FCM (Firebase)
    PushFcmNotification::dispatch(
        $userIds, 
        $title, 
        $message, 
        array_merge(['type' => $type], $data)
    );
}

}