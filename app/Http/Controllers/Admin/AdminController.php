<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\AdminActivityLog;
 

class AdminController extends Controller
{
    /**
     * Display a listing of admin users.
     */
    public function index(Request $request)
    {
        // 1. Base query: Only get users with admin roles
        $query = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'super_admin']);
            });

        // 2. Search by Name, Email, or Phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 3. Filter by Active Status
        if ($request->has('active_only')) {
            $query->where('is_active', true);
        }

        // 4. Execute with pagination
        $admins = $query->latest()->paginate(20);
        $admins->appends($request->query());

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
        // Use Validator with named error bag
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|unique:users,phone',  // Made optional
            'role' => 'nullable|in:admin,super_admin',  // Made optional with default
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'admin')  // ← Named error bag!
                ->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profiles', 'public');
        }

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone ?? null,
            'gender'        => $request->gender ?? null,
            'profile_image' => $imagePath,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Assign admin role - default to 'admin' if not provided
        $roleName = $request->role ?? 'admin';
        $adminRole = Role::findByName($roleName);
        $admin->assignRole($adminRole);

        return redirect()->back()
            ->with('success', 'تمت إضافة المدير بنجاح');
    }

    /**
     * Display the specified admin user.
     */
    public function show(User $admin)
    {
        // Ensure we're showing an admin
        if (!$admin->isAdmin() ) {
            abort(404);
        }


        $admin->load([
            'roles', 
            'createdCourses', 
            'sentNotifications', 
            'activities' => function($query) {
                $query->latest()->take(5);
            }
        ]);

        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin user.
     */
    public function edit(User $admin)
    {
        // Ensure we're editing an admin
        if (!$admin->isAdmin() ) {
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
        if (!$admin->isAdmin() ) {
            abort(404);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', Rule::unique('users')->ignoreModel($admin)],
            'email' => ['required', 'email', Rule::unique('users')->ignoreModel($admin)],
            'role' => 'required|in:admin,super_admin',
            'gender'       => 'required|in:ذكر,أنثي',
            'profile_image' => 'nullable|image|max:2048',
            'password' => 'nullable|min:8|confirmed',
            'remove_image' => 'nullable|boolean',
        ]);
        // Build the update data first
        $updateData = [
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'gender'   => $request->gender,
            'password' => $request->password ? Hash::make($request->password) : $admin->password,
        ];

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($admin->profile_image) {
                Storage::disk('public')->delete($admin->profile_image);
            }
            $updateData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        // Handle image removal checkbox
        if ($request->boolean('remove_image') && $admin->profile_image) {
            Storage::disk('public')->delete($admin->profile_image);
            $updateData['profile_image'] = null;
        }

        $admin->update($updateData);

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
        if (!$admin->isAdmin() ) {
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
        if (!$admin->isAdmin() ) {
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
     * Show admin's activity log (UPDATED - Now shows REAL data)
     */
    public function activity(User $admin)
    {
        // Verify admin exists and is actually an admin
        if (!$admin->isAdmin()) {
            abort(404);
        }
 
        // Fetch real activity logs for this admin
        // Paginate to avoid loading huge amounts of data
        $activities = AdminActivityLog::byAdmin($admin->id)
            ->newest()
            ->paginate(20);
 
        // Return view with real data
        return view('admin.admins.activity', compact('admin', 'activities'));
    }
 
    /**
     * Optional: Get recent activity across all admins
     */
    public function recentActivity(Request $request)
    {
        $query = AdminActivityLog::newest();
 
        // Filter by action if provided
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }
 
        // Filter by admin if provided
        if ($request->filled('admin_id')) {
            $query->byAdmin($request->admin_id);
        }
 
        // Filter by model type if provided
        if ($request->filled('model_type')) {
            $query->byModelType($request->model_type);
        }
 
        // Filter by date range if provided
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->betweenDates($request->from_date, $request->to_date);
        }
 
        $activities = $query->paginate(20);
        $admins = User::role('admin')->get();
 
        return view('admin.admins.recent-activity', compact('activities', 'admins'));
    }
 
    /**
     * Optional: Show only important activities (creates, deletes, approvals)
     */
    public function importantActivity(User $admin)
    {
        if (!$admin->isAdmin()) {
            abort(404);
        }
 
        $activities = AdminActivityLog::byAdmin($admin->id)
            ->importantActions()
            ->newest()
            ->paginate(20);
 
        return view('admin.admins.activity', compact('admin', 'activities'));
    }
 
    /**
     * Optional: Search activities
     */
    public function searchActivity(Request $request, User $admin)
    {
        if (!$admin->isAdmin()) {
            abort(404);
        }
 
        $search = $request->input('q', '');
 
        $activities = AdminActivityLog::byAdmin($admin->id)
            ->where(function ($query) use ($search) {
                $query->where('model_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%");
            })
            ->newest()
            ->paginate(20);
 
        return view('admin.admins.activity', compact('admin', 'activities'));
    }
 
        /**
         * Optional: Export activities as CSV
         */
        public function exportActivity(User $admin)
    {
        $activities = AdminActivityLog::byAdmin($admin->id)->newest()->get();
        
        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel UTF-8 Arabic support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['التاريخ', 'الوقت', 'النشاط', 'الهدف', 'النوع', 'الوصف']);

            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->created_at->format('Y-m-d'),
                    $activity->created_at->format('H:i:s'),
                    $activity->action_label,
                    $activity->model_name,
                    $activity->model_type_label, // Use the Arabic label accessor
                    $activity->description,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity.csv"',
        ]);
    }
}