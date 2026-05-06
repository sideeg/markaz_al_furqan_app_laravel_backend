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
        $search = $request->input('search');
        $courseId = $request->input('course_id');
        $studentId = $request->input('student_id');
        $courseType = $request->input('course_type');
        $status = $request->input('status'); // Removed 'pending' default to allow seeing all
        
        // Query enrollments with relationships
        $enrollments = Enrollment::with(['course', 'student', 'approver'])
            // 1. General Search (Student Name, Student ID, Course Name)
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('student', function($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                           ->orWhere('national_id', 'like', "%{$search}%");
                    })->orWhereHas('course', function($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
                });
            })
            // 2. Specific Filters
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

        // Append query strings so pagination links remember the filters
        $enrollments->appends($request->query());

        // ✅ FIXED: Fetch ALL courses so the admin can filter past enrollments
        $courses = Course::latest()->get(); 
        $students = User::withRole('student')->get();
        $statuses = ['pending' => 'قيد الانتظار', 'approved' => 'مقبول', 'rejected' => 'مرفوض'];

        return view('admin.enrollments.index', compact(
            'enrollments', 'courses', 'students', 'statuses'
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
    public function approve(Request $request,Enrollment $enrollment)
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

        return redirect()->route('admin.enrollments.index', $request->query())
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

        return redirect()->route('admin.enrollments.index', $request->query())
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

    /**
 * Show form to manually enroll a student.
 */
public function create(Request $request)
{
    $courses = Course::active()->get();
    $students = User::withRole('student')->get();
    $selectedCourse = $request->course_id ? Course::with('groups')->find($request->course_id) : null;

    return view('admin.enrollments.create', compact('courses', 'students', 'selectedCourse'));
}

/**
 * Manually enroll a student (admin action — auto-approved).
 */
public function store(Request $request)
{
    $request->validate([
        'course_id'  => 'required|exists:courses,id',
        'student_id' => 'required|exists:users,id',
        'group_id'   => 'nullable|exists:groups,id',
        'notes'      => 'nullable|string|max:500',
    ]);

    $course = Course::findOrFail($request->course_id);

    // Check for duplicate enrollment
    $exists = Enrollment::where('course_id', $request->course_id)
                        ->where('student_id', $request->student_id)
                        ->whereIn('status', ['pending', 'approved'])
                        ->exists();

    if ($exists) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'الطالب مسجل بالفعل في هذه الدورة');
    }

    // Check available slots
    if ($course->current_students >= $course->max_students) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'لا توجد أماكن متاحة في هذه الدورة');
    }

    DB::transaction(function () use ($request, $course) {
        $enrollment = Enrollment::create([
            'course_id'   => $request->course_id,
            'student_id'  => $request->student_id,
            'status'      => 'approved',   // admin enrollments are auto-approved
            'enrolled_at' => now(),
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'notes'       => $request->notes,
        ]);

        $course->increment('current_students');

        // Assign to group if selected
        if ($request->group_id) {
            $group = \App\Models\Group::find($request->group_id);
            if ($group) {
                $group->students()->syncWithoutDetaching([$request->student_id]);
                $group->increment('current_students');
            }
        }
    });

    return redirect()->route('admin.enrollments.index', ['status' => 'approved'])
        ->with('success', 'تم تسجيل الطالب في الدورة بنجاح');
}
}