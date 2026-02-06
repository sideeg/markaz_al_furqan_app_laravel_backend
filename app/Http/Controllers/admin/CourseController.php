<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Mosque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index()
    {
        $courses = $courses = Course::latest()->paginate(10); // Fetch all courses with pagination
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
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        //     'type' => 'required|in:online,open,closed',
        //     'image' => 'nullable|image|max:2048',
        //     'start_date' => 'required|date',
        //     'end_date' => 'required|date|after_or_equal:start_date',
        //     'max_students' => 'required|integer|min:1',
        //     'requirements' => 'nullable|string',
        //     'schedule_details' => 'nullable|string',
        // ],[],[],'courses');

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
}