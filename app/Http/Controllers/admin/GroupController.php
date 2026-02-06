<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class GroupController extends Controller
{
    /**
     * Display groups for a specific course.
     */
    public function index(Course $course)
    {
        $groups = $course->groups()
            ->withCount('students')
            ->with('creator')
            ->get();
            
        $unassignedStudents = $course->approvedStudents()
            ->whereDoesntHave('groups', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->get();

        return view('admin.groups.index', compact('course', 'groups', 'unassignedStudents'));
    }

    /**
     * Show the form for creating a new group.
     */
    public function create(Course $course)
    {
        $shiekhs = User::whereHas('roles', function ($query) {
            $query->where('name', 'sheikh');
        })->inRandomOrder()->get();
        return view('admin.groups.create', compact('course', 'shiekhs'));
    }

    /**
     * Store a newly created group.
     */
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_students' => 'required|integer|min:1',
            'schedule_details' => 'required|string|max:500',
            'sheikh_id' => 'nullable|exists:users,id',
        ]);

        Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'course_id' => $course->id,
            'max_students' => $request->max_students,
            'schedule_details' => $request->schedule_details,
            'sheikh_id' => $request->sheikh_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.courses.groups.index', $course)
            ->with('success', 'تم إنشاء المجموعة بنجاح');
    }

    /**
     * Display the specified group.
     */
    public function show(Course $course, Group $group)
    {
        $shiekh = $group->sheikh;
       
        $group->load('students', 'creator');
        
        $availableStudents = $course->approvedStudents()
            ->whereDoesntHave('groups', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->get();
        
        return view('admin.groups.show', compact('course', 'group', 'availableStudents', 'shiekh'));
    }

    /**
     * Show the form for editing the specified group.
     */
    public function edit(Course $course, Group $group)
    {
        $shiekhs = User::whereHas('roles', function ($query) {
            $query->where('name', 'sheikh');
        })->inRandomOrder()->get();
        return view('admin.groups.edit', compact('course', 'group', 'shiekhs'));
    }

    /**
     * Update the specified group.
     */
    public function update(Request $request, Course $course, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_students' => 'required|integer|min:1',
            'schedule_details' => 'required|string|max:500',
            'is_active' => 'boolean',
            'sheikh_id' => 'nullable|exists:users,id',
        ]);

        $group->update($request->all());

        return redirect()->route('admin.courses.groups.index', $course)
            ->with('success', 'تم تحديث المجموعة بنجاح');
    }

    /**
     * Remove the specified group.
     */
    public function destroy(Course $course, Group $group)
    {
        $group->delete();
        return redirect()->back()->with('success', 'تم حذف المجموعة بنجاح');
    }
    
    /**
     * Add student to group.
     */
    public function addStudent(Request $request, Course $course, Group $group)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);
        
        // Check if student is enrolled in course
        if (!$course->approvedStudents()->where('student_id', $request->student_id)->exists()) {
            return back()->with('error', 'الطالب غير مسجل في هذه الدورة');
        }
        
        // Check if group has available spots
        if ($group->students->count() >= $group->max_students) {
            return back()->with('error', 'لا توجد أماكن متاحة في هذه المجموعة');
        }
        
        // Check if student is already in a group for this course
        $studentGroups = DB::table('group_students')
            ->where('student_id', $request->student_id)
            ->whereExists(function ($query) use ($course) {
                $query->select(DB::raw(1))
                      ->from('groups')
                      ->whereColumn('groups.id', 'group_students.group_id')
                      ->where('groups.course_id', $course->id);
            })
            ->exists();
            
        if ($studentGroups) {
            return back()->with('error', 'الطالب مسجل بالفعل في مجموعة أخرى في هذه الدورة');
        }
        
        // Add student to group
        $group->students()->attach($request->student_id, [
            'assigned_at' => now(),
            'assigned_by' => auth()->id(),
        ]);
        
        // Update group student count
        $group->increment('current_students');
        
        return back()->with('success', 'تم إضافة الطالب إلى المجموعة بنجاح');
    }
    
    /**
     * Remove student from group.
     */
    public function removeStudent(Course $course, Group $group, User $student)
    {
        // Remove student from group
        $group->students()->detach($student);
        
        // Update group student count
        $group->decrement('current_students');
        
        return back()->with('success', 'تم إزالة الطالب من المجموعة بنجاح');
    }
    
    /**
     * Toggle group active status.
     */
    public function toggleStatus(Course $course, Group $group)
    {
        $group->update(['is_active' => !$group->is_active]);
        
        $status = $group->is_active ? 'تم تفعيل المجموعة' : 'تم تعطيل المجموعة';
        return back()->with('success', $status);
    }
}