<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::role('student')->latest()->paginate(10);
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:20',
            'qiraat' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'required|string|min:6|confirmed',
            'is_active' => 'nullable|boolean',
        ], [], [
            'name' => 'الاسم يجب ان يكون نص ليس اطول من 255 حرف',
            'email' => 'البريد الإلكتروني يجب ان يكون عنوان بريد إلكتروني صالح',
            'phone' => 'رقم الهاتف يجب ان يكون نص ليس اطول من 20 حرف',
            'national_id' => 'الرقم القومي يجب ان يكون نص ليس اطول من 20 حرف',
            'qiraat' => 'القراءة يجب ان تكون نص ليس اطول من 50 حرف',
            'profile_image' => 'صورة الملف الشخصي يجب ان تكون صورة',
            'password' => 'يجب ان تكون  علي الاقل ستة حروف و يجب ان تتكون من حروف وكلمات مرور',
            'is_active' => 'الحالة',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'student')
                ->withInput();
        }

        $data = $validator->validated();

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('students', 'public');
        }

        $data['password'] = Hash::make($request->password);
        $data['is_active'] = $request->has('is_active');

        $student = User::create($data);
        $student->assignRole('student');

        return redirect()->route('admin.students.index')->with('success', 'تم إضافة الطالب بنجاح');
    }

    public function show(User $student)
    {
        abort_unless($student->isStudent(), 404);
        return view('admin.students.show', compact('student'));
    }

    public function edit(User $student)
    {
        abort_unless($student->isStudent(), 404);
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:20',
            'qiraat' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('profile_image')) {
            if ($student->profile_image) {
                Storage::disk('public')->delete($student->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('students', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->has('is_active');
        $student->update($data);

        return redirect()->route('admin.students.index')->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    public function destroy(User $student)
    {
        abort_unless($student->isStudent(), 404);
        $student->delete();
        return back()->with('success', 'تم حذف الطالب بنجاح');
    }
    public function toggleStatus(User $student)
    {
        abort_unless($student->isStudent(), 404);

        $student->is_active = !$student->is_active;
        $student->save();

        return back()->with('success', 'تم تغيير حالة الطالب بنجاح');
    }
}
