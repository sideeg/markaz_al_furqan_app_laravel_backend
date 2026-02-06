<?php

namespace App\Services;


use App\Models\Course;
use App\Models\User;
use App\Models\Group;
use App\Models\Enrollment;
use App\Models\Evaluation;
use Illuminate\Support\Facades\DB;

class CourseService
{
    /**
     * Enroll a student in a course.
     *
     * @param int $courseId
     * @param int $studentId
     * @return Enrollment
     */
    public function enrollStudent(int $courseId, int $studentId)
    {
        return Enrollment::firstOrCreate([
            'course_id' => $courseId,
            'student_id' => $studentId,
        ]);
    }

    /**
     * Withdraw a student from a course.
     *
     * @param int $courseId
     * @param int $studentId
     * @return bool
     */
    public function withdrawStudent(int $courseId, int $studentId)
    {
        return Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->delete() > 0;
    }

    /**
     * Get a student's progress in a course.
     *
     * @param int $courseId
     * @param int $studentId
     * @return array
     */
    public function getStudentProgress(int $courseId, int $studentId)
    {
        // Example: Assume Enrollment has a 'progress' field (0-100)
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        return [
            'progress' => $enrollment ? $enrollment->progress : 0,
        ];
    }

    /**
     * Get a student's evaluations in a course.
     *
     * @param int $courseId
     * @param int $studentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentEvaluations(int $courseId, int $studentId)
    {
        return Evaluation::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->get();
    }
}