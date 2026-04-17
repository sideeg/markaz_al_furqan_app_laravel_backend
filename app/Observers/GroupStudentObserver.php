<?php
namespace App\Observers;

use App\Models\Group;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

/**
 * This observer fires when students are added to groups (pivot attach/sync)
 * 

 */
class GroupStudentObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Trigger when student is attached to group (new student added)
     */
    public function attaching(Group $group, $relatedId, $pivot = null): void
    {
        // Get the student being added
        $student = User::find($relatedId);
        $sheikh = $group->sheikh;
        Log::info('sheikh: ' . $sheikh);
        Log::info('student: ' . $student);
        if ($student && $sheikh) {
            // Notify sheikh that a new student was added to their group
            $this->notificationService->notifySheikhNewStudent(
                $sheikh->id,
                $student->name,
                $group->name,
                $group->id
            );
        }
    }

    /**
     * Trigger when student is detached from group (student removed)
     */
    public function detaching(Group $group, $relatedId): void
    {
        $student = User::find($relatedId);
        $sheikh = $group->sheikh;

        if ($student && $sheikh) {
            // Notify sheikh that a student was removed from their group
            $this->notificationService->notifySheikhStudentRemovedFromGroup(
                $sheikh->id,
                $student->name,
                $group->name,
                $group->id
            );
        }
    }
}