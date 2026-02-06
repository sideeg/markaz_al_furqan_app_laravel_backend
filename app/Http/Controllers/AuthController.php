<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Course;
use App\Models\HifzLog;
use App\Models\Notification;
use Carbon\Carbon;
use App\Models\Mosque;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        // Redirect to dashboard if already authenticated
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:student,sheikh,admin,supervisor',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Determine if input is email or phone
        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        // Prepare credentials
        $credentials = [
            $loginField => $request->email,
            'password' => $request->password,
            'is_active' => true
        ];

        // Attempt login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if user has the required role
            if (!$user->hasRole($request->role)) {
                Auth::logout();
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ليس لديك صلاحية الوصول بهذا النوع من المستخدمين'
                    ], 403);
                }
                
                return back()->withErrors([
                    'role' => 'ليس لديك صلاحية الوصول بهذا النوع من المستخدمين'
                ])->withInput();
            }

            $request->session()->regenerate();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'redirect' => $this->getRedirectUrl($user)
                ]);
            }

            return redirect()->intended($this->getRedirectUrl($user));
        }

        $errorMessage = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
        
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 401);
        }

        return back()->withErrors([
            'email' => $errorMessage
        ])->withInput();
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);
        }

        return redirect()->route('login');
    }

    /**
     * Show dashboard based on user role.
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        return redirect($this->getRedirectUrl($user));
    }

    /**
     * Get redirect URL based on user role.
     */
    private function getRedirectUrl($user)
    {
        if ($user->hasRole('supervisor')) {
            return '/supervisor/dashboard';
        } elseif ($user->hasRole('admin')) {
            return '/admin/dashboard';
        } elseif ($user->hasRole('sheikh')) {
            return '/sheikh/dashboard';
        } else {
            return '/student/dashboard';
        }
    }

    /**
     * Redirect to appropriate dashboard.
     */
    private function redirectToDashboard()
    {
        $user = Auth::user();
        return redirect($this->getRedirectUrl($user));
    }

    /**
     * Show student dashboard.
     */
    public function studentDashboard()
    {
        $this->authorize('student');
        
        $user = Auth::user();
        $enrolledCourses = $user->enrolledCourses()->with('groups')->get();
        $recentLogs = $user->hifzLogLogs()->with('course')->latest()->take(5)->get();
        $totalMemorized = $user->total_memorized_ayahs;
        $averageEvaluation = $user->average_evaluation;

        return view('dashboards.student', compact(
            'user', 'enrolledCourses', 'recentLogs', 'totalMemorized', 'averageEvaluation'
        ));
    }

    /**
     * Show sheikh dashboard.
     */
    public function sheikhDashboard()
    {
        $this->authorize('sheikh');
        
        $user = Auth::user();
        $teachingCourses = $user->teachingCourses()->with('students')->get();
        $students = $user->teachingCourses()->with('students')->get()->pluck('students')->flatten()->unique();
        $recentLogs = $user->createdHifzLogLogs()->with(['student', 'course'])->latest()->take(10)->get();

        return view('dashboards.sheikh', compact(
            'user', 'teachingCourses', 'students', 'recentLogs'
        ));
    }

    /**
     * Show admin dashboard.
     */
    public function adminDashboard()
    {
        $this->authorize('admin');
        
        // $totalUsers = User::count();
        // $totalStudents = User::whereHas('roles', function($q) { $q->where('name', 'student'); })->count();
        // $totalSheikhs = User::whereHas('roles', function($q) { $q->where('name', 'sheikh'); })->count();
        // $activeUsers = User::where('is_active', true)->count();

        // return view('dashboards.admin', compact(
        //     'totalUsers', 'totalStudents', 'totalSheikhs', 'activeUsers'
        // ));

        // 1. Admin Info
        $adminName = auth()->user()->name;
        $arabicDate = Carbon::now()->locale('ar')->isoFormat('dddd، D MMMM Y');

        // 2. Statistics Cards (with dummy growth percentages)
        $stats = [
            'studentsCount' => [
                'count' => User::whereHas('roles', function($q) { $q->where('name', 'student'); })->count(),
                'growth' => '+5.6%'
            ],
            'sheikhsCount' => [
                'count' => User::whereHas('roles', function($q) { $q->where('name', 'sheikh'); })->count(),
                'growth' => '+2.3%'
            ],
            'coursesCount' => [
                'count' => Course::count(),
                'growth' => '+8.1%'
            ],
            'activeCoursesCount' => [
                'count' => Course::where('is_active', true)->count(),
                'growth' => '+3.4%'
            ],
        ];


        // 3. Recent Courses (last 5)
        $recentCourses = Course::latest()
            ->take(5)
            ->get(['id', 'name', 'type', 'is_registration_open', 'start_date']);

        // 4. Recent HifzLog Logs (last 5)
        $recentHifzLog = HifzLog::with(['student:id,name', 'sheikh:id,name'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                // Convert numeric evaluation to stars (1-5)
                $stars = str_repeat('★', (int)$item->evaluation);
                $stars .= str_repeat('☆', 5 - (int)$item->evaluation);

                
                return [
                    'id' => $item->id,
                    'student_name' => $item->student->name,
                    'sheikh_name' => $item->sheikh->name,
                    'date' => $item->session_date,
                    'stars' => $stars,
                    
                ];
            });

        // 5. Notifications (latest 5)
        $notifications = Notification::latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'message' => \Illuminate\Support\Str::limit($item->message, 30),
                    'target_group' => $item->target_group,
                    'created_at' => $item->created_at->diffForHumans(),
                ];
            });
        // 6. Mosques 
        $mosques = Mosque::all();

        return view('dashboards.admin', compact(
            'adminName',
            'arabicDate',
            'stats',
            'recentCourses',
            'recentHifzLog',
            'notifications',
            'mosques',
            
        ));
    }

    /**
     * Show supervisor dashboard.
     */
    public function supervisorDashboard()
    {
        $this->authorize('supervisor');
        
        // Get comprehensive statistics
        $totalUsers = User::count();
        $totalStudents = User::whereHas('roles', function($q) { $q->where('name', 'student'); })->count();
        $totalSheikhs = User::whereHas('roles', function($q) { $q->where('name', 'sheikh'); })->count();
        $totalAdmins = User::whereHas('roles', function($q) { $q->where('name', 'admin'); })->count();
        $activeUsers = User::where('is_active', true)->count();

        return view('dashboards.supervisor', compact(
            'totalUsers', 'totalStudents', 'totalSheikhs', 'totalAdmins', 'activeUsers'
        ));
    }

    /**
     * Authorize role access.
     */
    private function authorize($role)
    {
        if (!Auth::user()->hasRole($role)) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }
    }
}