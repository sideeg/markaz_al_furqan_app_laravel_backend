<?php
namespace App\Observers;
 
use App\Models\Mosque;
use App\Services\ActivityLogger;
 
class MosqueObserver
{
    /**
     * Handle the Mosque "created" event.
     */
    public function created(Mosque $mosque): void
    {
        ActivityLogger::logCreate($mosque);
    }
 
    /**
     * Handle the Mosque "updated" event.
     */
    public function updated(Mosque $mosque): void
    {
        $changes = $mosque->getChanges();
        
        if (!empty($changes)) {
            ActivityLogger::logUpdate($mosque, $changes);
        }
    }
 
    /**
     * Handle the Mosque "deleted" event.
     */
    public function deleted(Mosque $mosque): void
    {
        ActivityLogger::logDelete($mosque);
    }
}