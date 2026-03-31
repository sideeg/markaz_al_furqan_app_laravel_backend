<?php

namespace App\Observers;

use App\Models\Course;

use App\Services\ActivityLogger;
 
class CourseObserver
{
    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        ActivityLogger::logCreate($course);
    }
 
    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        // Get only changed fields
        $changes = $course->getChanges();
        
        if (!empty($changes)) {
            ActivityLogger::logUpdate($course, $changes);
        }
    }
 
    /**
     * Handle the Course "deleted" event.
     */
    public function deleted(Course $course): void
    {
        ActivityLogger::logDelete($course);
    }
}
