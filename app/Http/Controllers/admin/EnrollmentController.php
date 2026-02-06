<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollment requests.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $courseId = $request->input('course_id');
        $studentId = $request->input('student_id');
        $courseType = $request->input('course_type');
        $status = $request->input('status', 'pending');
        
        // Query enrollments with relationships
        $enrollments = Enrollment::with(['course', 'student', 'approver'])
            ->when($courseId, function ($query) use ($courseId) {
                return $query->where('course_id', $courseId);
            })
            ->when($studentId, function ($query) use ($studentId) {
                return $query->where('student_id', $studentId);
            })
            ->when($courseType, function ($query) use ($courseType) {
                return $query->whereHas('course', function ($q) use ($courseType) {
                    $q->where('type', $courseType);
                });
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        // Get filter options
        $courses = Course::where('is_registration_open', true)->get();
        $students = User::withRole('student')->get();
        $statuses = ['pending' => 'قيد الانتظار', 'approved' => 'مقبول', 'rejected' => 'مرفوض'];

        return view('admin.enrollments.index', compact(
            'enrollments',
            'courses',
            'students',
            'statuses',
            'courseId',
            'studentId',
            'courseType',
            'status'
        ));
    }

    /**
     * Display the specified enrollment request.
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load([
            'course',
            'student',
            'student.enrolledCourses' => function ($query) {
                $query->where('status', 'approved');
            },
            'student.hifzLogs' => function ($query) {
                $query->latest()->limit(5);
            },
            'student.reviewLogs' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return view('admin.enrollments.show', compact('enrollment'));
    }

    /**
     * Approve an enrollment request.
     */
    public function approve(Enrollment $enrollment)
    {
        // Check if course has available spots
        $course = $enrollment->course;
        if ($course->current_students >= $course->max_students) {
            return redirect()->back()
                ->with('error', 'لا توجد أماكن متاحة في هذه الدورة');
        }

        DB::transaction(function () use ($enrollment) {
            $enrollment->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            // Increase course student count
            $enrollment->course()->increment('current_students');
        });

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'تم قبول طلب التسجيل بنجاح');
    }

    /**
     * Reject an enrollment request.
     */
    public function reject(Enrollment $enrollment, Request $request)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $enrollment->update([
            'status' => 'rejected',
            'notes' => $request->rejection_reason,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'تم رفض طلب التسجيل بنجاح');
    }

    /**
     * Delete an enrollment request.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return redirect()->back()->with('success', 'تم حذف طلب التسجيل بنجاح');
    }
}