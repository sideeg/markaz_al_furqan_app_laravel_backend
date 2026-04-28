<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Mosque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;

class CourseController extends Controller
{
    private NotificationService $notificationService;
        public function __construct()
    {
        
        $this->notificationService = new NotificationService();
    }

    /**
     * Display a listing of the courses.
     */
    public function index(Request $request)
    {
        $query = Course::query();

        // 1. Search by name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Filter by Active Status (from the toggle switch)
        if ($request->has('active_only')) {
            $query->where('is_active', true);
        }

        // 3. Execute with pagination and preserve queries
        $courses = $query->latest()->paginate(10);
        $courses->appends($request->query());

        $mosques = Mosque::all();
        
        return view('admin.courses.index', compact('courses', 'mosques'));
    }

    // Add to CourseController

/**
 * Show the form for creating a new course.
 */
    public function create()
    {
        $mosques = Mosque::all();
        return view('admin.courses.create', compact('mosques'));
    }

    /**
     * Toggle course active status.
     */
    public function toggleStatus(Course $course)
    {
        $course->update(['is_active' => !$course->is_active]);
        
        $status = $course->is_active ? 'تم تفعيل الدورة' : 'تم تعطيل الدورة';
        return back()->with('success', $status);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:online,open,closed',
            'image' => 'nullable|image|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_students' => 'required|integer|min:1',
            'requirements' => 'nullable|string',
            'schedule_details' => 'nullable|string',
        ],[],[],'courses');
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'course')
                ->withInput();
        }
        $mosque_id = $request->mosque_id ?? null;
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('courses', 'public');
        }

        Course::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'mosque_id' => $request->mosque_id,
            'image_path' => $imagePath,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_students' => $request->max_students,
            'current_students' => 0,
            'is_active' => true,
            'is_registration_open' => true,
            'requirements' => $request->requirements,
            'schedule_details' => $request->schedule_details,
            'created_by' => Auth::id(),
        ]);
        $this->notificationService->notifyNewCourse(Course::latest()->first()->id, $request->name);
        return redirect()->back()->with('success', 'تمت إضافة الدورة بنجاح');
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $mosques = Mosque::all();
        return view('admin.courses.edit', compact('course', 'mosques'));
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:online,open,closed',
            'mosque_id' => 'nullable|exists:mosques,id',
            'image' => 'nullable|image|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_students' => 'required|integer|min:1',
            'requirements' => 'nullable|string',
            'schedule_details' => 'nullable|string',
        ]);
        if ($request->hasFile('image')) {
            $course->image_path = $request->file('image')->store('courses', 'public');
        }
        

        $course->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'mosque_id' => $request->mosque_id,
            'image_path' => $course->image_path,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_students' => $request->max_students,
            'requirements' => $request->requirements,
            'schedule_details' => $request->schedule_details,
            'is_registration_open' => $request->has('is_registration_open') ? $request->is_registration_open : $course->is_registration_open,
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'تم تحديث الدورة بنجاح');
    }

    /**
     * Display the specified course.
     */
    public function show($id)
    {
        $course = Course::with('mosque')->findOrFail($id);
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Remove the specified course.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->back()->with('success', 'تم حذف الدورة بنجاح');
    }

   

public function markCompleted(Course $course, NotificationService $notificationService)
{
    // Prevent double execution
    if ($course->is_completed) {
        return back()->with('error', 'هذه الدورة مكتملة بالفعل.'); // "Course is already completed"
    }

    // 1. Update DB (Course & Enrollments)
    $course->markAsCompleted();

    // 2. Fire Notifications to Sheikhs and Students
    $notificationService->notifyCourseCompleted($course);

    return back()->with('success', 'تم إنهاء الدورة وإرسال الإشعارات بنجاح.'); // "Course ended and notifications sent"
}

public function toggleCompletion(Course $course, NotificationService $notificationService)
{
    if ($course->is_completed) {
        $course->markAsIncomplete();
        return back()->with('success', 'تم إلغاء اكتمال الدورة وإعادة فتحها بنجاح.');
    } else {
        $course->markAsCompleted();
        // Send the notification automatically to students and sheikhs
        $notificationService->notifyCourseCompleted($course);
        
        return back()->with('success', 'تم إنهاء الدورة وإرسال الإشعارات للطلاب والمشايخ بنجاح.');
    }
}
}