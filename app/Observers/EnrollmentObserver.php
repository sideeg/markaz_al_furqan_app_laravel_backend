<?php
namespace App\Observers;
 
use App\Models\Enrollment;
use App\Services\ActivityLogger;
 
class EnrollmentObserver
{
    /**
     * Handle the Enrollment "updated" event.
     * Log when enrollment status changes (approve/reject)
     */
    public function updated(Enrollment $enrollment): void
    {
        // Only log if status changed
        if ($enrollment->wasChanged('status')) {
            $newStatus = $enrollment->status;
            
            if ($newStatus === 'approved') {
                $course = $enrollment->course;
                $student = $enrollment->student;
                ActivityLogger::logEnrollmentApproved($enrollment, $course, $student);
            } elseif ($newStatus === 'rejected') {
                $course = $enrollment->course;
                $student = $enrollment->student;
                ActivityLogger::logEnrollmentRejected($enrollment, $course, $student);
            }
        }
    }
}