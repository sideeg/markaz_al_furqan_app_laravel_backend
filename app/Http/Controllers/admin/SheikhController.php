<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SheikhController extends Controller
{
    /**
     * Display a listing of sheikhs.
     */
    public function index()
    {
        $sheikhs = User::with('teachingCourses', 'teachingGroups')
            ->withRole('sheikh')
            ->latest()
            ->paginate(20);

        return view('admin.sheikhs.index', compact('sheikhs'));
    }

    /**
     * Show the form for creating a new sheikh.
     */
    public function create()
    {
        return view('admin.sheikhs.create');
    }

    /**
     * Store a newly created sheikh.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'national_id' => 'required|unique:users,national_id',
            'qiraat' => 'required|string|max:50',
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'required|min:8|confirmed',
        ]);

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profiles', 'public');
        }

        $sheikh = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'qiraat' => $request->qiraat,
            'profile_image' => $imagePath,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);
        // Assign sheikh role
        $sheikhRole = Role::findByName('sheikh');
        $sheikh->assignRole($sheikhRole);

        return redirect()->route('admin.sheikhs.index')
            ->with('success', 'تمت إضافة الشيخ بنجاح');
    }

    /**
     * Display the specified sheikh.
     */
    public function show(User $sheikh)
    {
        // Ensure we're showing a sheikh
        if (!$sheikh->isSheikh()) {
            abort(404);
        }

        $sheikh->load([
            'teachingCourses',
            'teachingGroups',
            'createdHifzLogs' => fn($q) => $q->latest()->limit(5),
            'createdReviewLogs' => fn($q) => $q->latest()->limit(5)
        ]);

        return view('admin.sheikhs.show', compact('sheikh'));
    }

    /**
     * Show the form for editing the specified sheikh.
     */
    public function edit(User $sheikh)
    {
        // Ensure we're editing a sheikh
        if (!$sheikh->isSheikh()) {
            abort(404);
        }

        return view('admin.sheikhs.edit', compact('sheikh'));
    }

    /**
     * Update the specified sheikh.
     */
    public function update(Request $request, User $sheikh)
    {
        // Ensure we're updating a sheikh
        if (!$sheikh->isSheikh()) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($sheikh->id)
            ],
            'phone' => [
                'required',
                Rule::unique('users')->ignore($sheikh->id)
            ],
            'national_id' => [
                'required',
                Rule::unique('users')->ignore($sheikh->id)
            ],
            'qiraat' => 'required|string|max:50',
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($sheikh->profile_image) {
                Storage::disk('public')->delete($sheikh->profile_image);
            }
            $sheikh->profile_image = $request->file('profile_image')->store('profiles', 'public');
        }

        $sheikh->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'qiraat' => $request->qiraat,
            'password' => $request->password ? Hash::make($request->password) : $sheikh->password,
        ]);

        return redirect()->route('admin.sheikhs.index')
            ->with('success', 'تم تحديث بيانات الشيخ بنجاح');
    }

    /**
     * Remove the specified sheikh.
     */
    public function destroy(User $sheikh)
    {
        // Ensure we're deleting a sheikh
        if (!$sheikh->isSheikh()) {
            abort(404);
        }

        // Delete profile image if exists
        if ($sheikh->profile_image) {
            Storage::disk('public')->delete($sheikh->profile_image);
        }

        $sheikh->delete();

        return redirect()->route('admin.sheikhs.index')
            ->with('success', 'تم حذف الشيخ بنجاح');
    }
    
    /**
     * Toggle sheikh active status.
     */
    public function toggleStatus(User $sheikh)
    {
        // Ensure we're toggling a sheikh
        if (!$sheikh->isSheikh()) {
            abort(404);
        }

        $sheikh->update(['is_active' => !$sheikh->is_active]);
        
        $status = $sheikh->is_active ? 'تم تفعيل الشيخ' : 'تم تعطيل الشيخ';
        return back()->with('success', $status);
    }
    
    /**
     * Show sheikh's teaching schedule.
     */
    public function schedule(User $sheikh)
    {
        if (!$sheikh->isSheikh()) {
            abort(404);
        }

        $schedule = $sheikh->teachingGroups()
            ->with(['course', 'students'])
            ->get()
            ->groupBy('day_of_week');

        return view('admin.sheikhs.schedule', compact('sheikh', 'schedule'));
    }
}