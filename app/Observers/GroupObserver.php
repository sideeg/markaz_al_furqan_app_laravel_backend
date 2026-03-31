<?php
namespace App\Observers;
 
use App\Models\Group;
use App\Services\ActivityLogger;
 
class GroupObserver
{
    /**
     * Handle the Group "created" event.
     */
    public function created(Group $group): void
    {
        ActivityLogger::logCreate($group);
    }
 
    /**
     * Handle the Group "updated" event.
     */
    public function updated(Group $group): void
    {
        $changes = $group->getChanges();
        
        if (!empty($changes)) {
            ActivityLogger::logUpdate($group, $changes);
        }
    }
 
    /**
     * Handle the Group "deleted" event.
     */
    public function deleted(Group $group): void
    {
        ActivityLogger::logDelete($group);
    }
}