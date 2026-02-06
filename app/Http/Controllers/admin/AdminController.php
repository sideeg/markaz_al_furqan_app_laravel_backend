<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Display a listing of admin users.
     */
    public function index()
    {
        $admins = User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            })
            ->latest()
            ->paginate(20);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create()
    {
        $roles = Role::whereIn('name', ['admin', 'super_admin'])->get();
        return view('admin.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'role' => 'required|in:admin,super_admin',
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'required|min:8|confirmed',
        ]);

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profiles', 'public');
        }

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'profile_image' => $imagePath,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Assign admin role
        $adminRole = Role::findByName($request->role);
        $admin->assignRole($adminRole);

        return redirect()->route('admin.admins.index')
            ->with('success', 'تمت إضافة المدير بنجاح');
    }

    /**
     * Display the specified admin user.
     */
    public function show(User $admin)
    {
        // Ensure we're showing an admin
        if (!$admin->isAdmin() && !$admin->isSuperAdmin()) {
            abort(404);
        }

        $admin->load(['roles', 'createdCourses', 'sentNotifications']);

        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin user.
     */
    public function edit(User $admin)
    {
        // Ensure we're editing an admin
        if (!$admin->isAdmin() && !$admin->isSuperAdmin()) {
            abort(404);
        }

        $roles = Role::whereIn('name', ['admin', 'super_admin'])->get();
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified admin user.
     */
    public function update(Request $request, User $admin)
    {
        // Ensure we're updating an admin
        if (!$admin->isAdmin() && !$admin->isSuperAdmin()) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($admin->id)
            ],
            'phone' => [
                'required',
                Rule::unique('users')->ignore($admin->id)
            ],
            'role' => 'required|in:admin,super_admin',
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($admin->profile_image) {
                Storage::disk('public')->delete($admin->profile_image);
            }
            $admin->profile_image = $request->file('profile_image')->store('profiles', 'public');
        }

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password ? Hash::make($request->password) : $admin->password,
        ]);

        // Update role if changed
        if ($admin->roles->first()->name !== $request->role) {
            $admin->syncRoles([$request->role]);
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'تم تحديث بيانات المدير بنجاح');
    }

    /**
     * Remove the specified admin user.
     */
    public function destroy(User $admin)
    {
        // Ensure we're deleting an admin
        if (!$admin->isAdmin() && !$admin->isSuperAdmin()) {
            abort(404);
        }

        // Prevent deletion of current user
        if ($admin->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'لا يمكنك حذف حسابك الخاص');
        }

        // Delete profile image if exists
        if ($admin->profile_image) {
            Storage::disk('public')->delete($admin->profile_image);
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'تم حذف المدير بنجاح');
    }
    
    /**
     * Toggle admin active status.
     */
    public function toggleStatus(User $admin)
    {
        // Ensure we're toggling an admin
        if (!$admin->isAdmin() && !$admin->isSuperAdmin()) {
            abort(404);
        }

        // Prevent deactivation of current user
        if ($admin->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'لا يمكنك تعطيل حسابك الخاص');
        }

        $admin->update(['is_active' => !$admin->is_active]);
        
        $status = $admin->is_active ? 'تم تفعيل المدير' : 'تم تعطيل المدير';
        return back()->with('success', $status);
    }
    
    /**
     * Show admin's activity log.
     */
    public function activity(User $admin)
    {
        if (!$admin->isAdmin() && !$admin->isSuperAdmin()) {
            abort(404);
        }

        // In a real application, you would fetch actual activity logs
        $activities = [
            ['action' => 'أنشأ دورة جديدة', 'target' => 'دورة الفرقان', 'time' => '2023-06-25 14:30'],
            ['action' => 'قام بتحديث', 'target' => 'مسجد الرحمة', 'time' => '2023-06-24 10:15'],
            ['action' => 'أرسل إشعار', 'target' => 'لجميع الطلاب', 'time' => '2023-06-23 16:45'],
            ['action' => 'عيّن شيخًا', 'target' => 'الشيخ أحمد', 'time' => '2023-06-22 09:20'],
            ['action' => 'صدر تقرير', 'target' => 'تقرير الحفظ الشهري', 'time' => '2023-06-21 11:30'],
        ];

        return view('admin.admins.activity', compact('admin', 'activities'));
    }
}