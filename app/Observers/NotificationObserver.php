<?php
namespace App\Observers;
 
use App\Models\Notification;
use App\Services\ActivityLogger;
 
class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     * Log when notification is sent (when sent_at is set)
     */
    public function created(Notification $notification): void
    {
        // Only log if notification is actually being sent
        if ($notification->sent_at) {
            ActivityLogger::logNotificationSent($notification);
        }
    }
 
    /**
     * Handle the Notification "updated" event.
     * Log when notification is sent (if sent_at just got set)
     */
    public function updated(Notification $notification): void
    {
        // Check if sent_at was just set
        if ($notification->wasChanged('sent_at') && $notification->sent_at) {
            ActivityLogger::logNotificationSent($notification);
        }
    }
}