<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\HifzLog;
use App\Models\ReviewLog;
use Illuminate\Support\Facades\DB;


class SheikhController extends Controller
{
    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة'],
                ]);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'حسابك غير مفعل. يرجى التواصل مع الإدارة',
                ], 403);
            }

            if ($user->role !== 'sheikh') {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الحساب ليس شيخًا',
                ], 403);
            }
            // Revoke all existing tokens
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'token' => $token,
                    'minimum_required_version' => '2.0.0',
                ],
            ]);

        } catch (ValidationException $e) {
            Log::error('Sheikh login validation error: ', [$e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Sheikh login error: ',[ $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => auth()->user()->load(['courses', 'groups']),
        ]);
    }

    public function myCourses(Request $request)
{
    $groups = Group::where('sheikh_id', auth()->id())
                  ->with(['course.mosque']) // تحميل البيانات المطلوبة
                  ->get();
    
   

    if ($groups->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'لا توجد مجموعات لهذا الشيخ',
            'data' => []
        ], 200);
    }

    // تجميع الدورات الفريدة
    $coursesData = [];
    $processedCourses = [];
    $groupsdata = [];

    foreach ($groups as $group) {
        $course = $group->course;
        
        // تجنب تكرار الدورات
        if (!in_array($course->id, $processedCourses)) {
            $processedCourses[] = $course->id;
            
            // حساب عدد المجموعات للشيخ في هذه الدورة
            $groupsCount = Group::where('course_id', $course->id)
                              ->where('sheikh_id', auth()->id())
                              ->count();
            

            $coursesData[] = [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'start_date' => $course->start_date ? $course->start_date->format('Y-m-d') : null,
                'end_date' => $course->end_date ? $course->end_date->format('Y-m-d') : null,
                'type' => $course->type,
                'type_display_name' => $course->type_display_name, // من accessor
                'image_url' => $course->image_url, // من accessor
                'max_students' => $course->max_students,
                'current_students' => $course->current_students,
                'is_active' => $course->is_active,
                'is_registration_open' => $course->is_registration_open,
                'schedule_details' => $course->schedule_details,
                'groups_count' => $groupsCount,
                'enrollment_percentage' => $course->enrollment_percentage, // من accessor
                'available_slots' => $course->available_slots, // من accessor
                'can_enroll' => $course->can_enroll, // من accessor
                'mosque' => $course->mosque ? [
                    'id' => $course->mosque->id,
                    'name' => $course->mosque->name
                ] : null,
                'created_by' => $course->created_by,
                'created_at' => $course->created_at,
                'updated_at' => $course->updated_at,
            ];
        }
    }

    return response()->json([
        'success' => true,
        'data' => $coursesData,
    ]);
}

    public function groupStudents(Request $request, int $course_id, int $group_id)
    {
        $student = Group::findOrFail($group_id)->students();
        return response()->json([
            'success' => true,
            'data' => $student->paginate($request->get('per_page', 15))->map(function ($user) {
                return $this->formatUserData($user);
            }),
        ])->withHeaders([
            'X-Total-Count' => $student->count(),
            'X-Total-Pages' => ceil($student->count() / $request->get('per_page', 15)),
            'X-Current-Page' => $request->get('page', 1),
            'X-Per-Page' => $request->get('per_page', 15),
        ])->setStatusCode(200);
    }       
       
    

    public function courseGroups(Request $request, int $course_id)
    {
        $groups= Group::where('course_id', $course_id)->where('sheikh_id', auth()->id())->with('students')->get();
        if ($groups->isEmpty()) {
           
            return response()->json([
                'success' => false,
                'message' => 'لا توجد مجموعات لهذه الدورة',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

   // Backend Controller Method - Improved Version
public function myStudents()
{
    Log::info('Fetching my students...');
    $sheikh_id = auth()->id();
    Log::info('Sheikh ID: ' . $sheikh_id);
    // Get groups with their related data
    $groups = Group::where('sheikh_id', $sheikh_id)
        ->with(['students', 'course'])
        ->get();
    Log::info('Groups fetched successfully.');
    
    // Transform the data to include group and course info for each student
    $studentsWithDetails = [];
    
    foreach ($groups as $group) {
        foreach ($group->students as $student) {
            $studentsWithDetails[] = [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone ?? null,
                'created_at' => $student->created_at,
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description ?? null,
                ],
                'course' => [
                    'id' => $group->course->id,
                    'name' => $group->course->name,
                    'description' => $group->course->description ?? null,
                ]
            ];
            
            Log::info('Student data transformed successfully.');
        }
        Log::info('Group data transformed successfully.');
    }
    
    // Get courses for additional info if needed
    $courses = Course::whereIn('id', $groups->pluck('course_id'))->get();
    Log::info('Courses fetched successfully.');
    return response()->json([
        'success' => true,
        'data' => [
            'students' => $studentsWithDetails,
            'total_students' => count($studentsWithDetails),
            'total_groups' => $groups->count(),
            'courses' => $courses,
        ]
    ]);
}
    public function studentProgress(User $student)
    {
        return $student->courses->map->pivot->progress;
    }

    public function studentEvaluations(User $student)
    {
        return $student->courses->map->pivot->evaluations;
    }

    public function statistics()
    {
        return [
            'total_students' => User::whereHas('courses', function ($query) {
                $query->where('user_id', auth()->id());
            })->count(),
            'total_courses' => Course::where('user_id', auth()->id())->count(),
            'total_enrollments' => CourseEnrollment::whereHas('course', function ($query) {
                $query->where('user_id', auth()->id());
            })->count(),
        ];
    }

    public function studentsReport()
    {
        return User::whereHas('courses', function ($query) {
            $query->where('user_id', auth()->id());
        })->get()->map(function ($student) {
            return [
                'name' => $student->name,
                'email' => $student->email,
                'courses' => $student->courses->pluck('name')->implode(', '),
                'progress' => $student->courses->map->pivot->progress->avg(),
            ];
        });
    }

    public function progressReport()
    {
        return Course::where('user_id', auth()->id())->get()->map(function ($course) {
            return [
                'name' => $course->name,
                'students' => $course->students->pluck('name')->implode(', '),
                'progress' => $course->students->map->pivot->progress->avg(),
            ];
        });
    }

    public function logout()
    {
        $user = auth()->user();
        if ($user) {
            $user->tokens()->delete();
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لم يتم العثور على مستخدم مسجل الدخول',
        ], 401);
    }

    public function updateProfile(Request $request)
    {
        
        $user = auth()->user();
        // Handle password update separately
        if ($request->has('current_password')) {
            // Validate password fields
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed:new_password_confirmation',
                'new_password_confirmation' => 'required|string',
            ]);
            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور الحالية غير صحيحة',
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث كلمة المرور بنجاح',
                'data' => $this->formatUserData($user),
            ]);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:20',
            'qiraat' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data' => $this->formatUserData($user),
        ]);
    }

    public function getProfile()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على مستخدم مسجل الدخول',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatUserData($user),
        ]);
    }

    /**
     * Format user data for response.
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'national_id' => $user->national_id,
            'qiraat' => $user->qiraat,
            'profile_image' => $user->profile_image_url,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    public function getDashboard(Request $request)
    {
        $sheikhId = auth()->id();

        $totalStudents = User::role('student')
            ->whereHas('groups', function($q) use ($sheikhId) {
                $q->where('sheikh_id', $sheikhId);
            })
            ->distinct()
            ->count();

        $totalCourses = Course::whereHas('groups', function($q) use ($sheikhId) {
            $q->where('sheikh_id', $sheikhId);
        })
        ->distinct()
        ->count();

        $totalGroups = Group::where('sheikh_id', $sheikhId)->count();

        // ── Unread notification count ─────────────────────────────────────────
    // Injected here to save the Flutter app a separate network request.
    // Uses the same role-aware logic from NotificationService.
    $notificationService = app(\App\Services\NotificationService::class);
    $unreadCount = $notificationService->getUnreadCount($sheikhId);

        // ── Recent hifz logs ──────────────────────────────────────────
        $recentHifzLogs = HifzLog::where('sheikh_id', $sheikhId)
            ->with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->filter(fn($log) => $log->student !== null) // ← skip orphaned logs
            ->map(function($log) {
                return [
                    'id'         => $log->id,
                    'surah_name' => $log->start_sura,
                    'from_ayah'  => $log->start_ayah,
                    'to_ayah'    => $log->end_ayah,
                    'date'       => $log->date,
                    'created_at' => $log->created_at,
                    'student'    => [
                        'id'   => $log->student->id,
                        'name' => $log->student->name,
                    ],
                    'course'     => [
                        'id'   => $log->course?->id   ?? 0,         // ← null-safe
                        'name' => $log->course?->name ?? 'غير محدد', // ← null-safe
                    ],
                ];
            })
            ->values(); // re-index after filter

        // ── Recent review logs ────────────────────────────────────────
        $recentReviewLogs = ReviewLog::where('sheikh_id', $sheikhId)
            ->with(['student'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->filter(fn($log) => $log->student !== null) // ← skip orphaned logs
            ->map(function($log) {
                return [
                    'id'         => $log->id,
                    'surah_name' => $log->surah,
                    'from_ayah'  => $log->start_ayah,
                    'to_ayah'    => $log->end_ayah,
                    'date'       => $log->date,
                    'created_at' => $log->created_at,
                    'student'    => [
                        'id'   => $log->student->id,
                        'name' => $log->student->name,
                    ],
                    'course'     => [
                        'id'   => 0,
                        'name' => 'مراجعة عامة',
                    ],
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'stats' => [
                    'total_students' => $totalStudents,
                    'total_courses'  => $totalCourses,
                    'total_groups'   => $totalGroups,
                ],
                'recent_hifz_logs'   => $recentHifzLogs,
                'recent_review_logs' => $recentReviewLogs,
                 'unread_notifications_count'  => $unreadCount,
            ]
        ]);
    }

    

// ─────────────────────────────────────────────────────────────────────────────
// GET LOGS
// Route: GET /sheikh/logs
// Params: type (all|hifz|review), search, from_date, to_date, per_page, page
// ─────────────────────────────────────────────────────────────────────────────

public function getLogs(Request $request): JsonResponse
{
    $sheikhId = auth()->id();
    $type     = $request->get('type', 'all');       // all | hifz | review
    $search   = $request->get('search', '');
    $fromDate = $request->get('from_date');          // Y-m-d
    $toDate   = $request->get('to_date');            // Y-m-d
    $perPage  = (int) $request->get('per_page', 20);

    // ── Summary counts (not affected by filters) ──────────────────────────
    $weekStart  = now()->startOfWeek()->toDateString();
    $monthStart = now()->startOfMonth()->toDateString();
    $today      = now()->toDateString();

    $hifzThisWeek  = HifzLog::where('sheikh_id', $sheikhId)
        ->where(DB::raw('`session_date`'), '>=', $weekStart)->count();
    $hifzThisMonth = HifzLog::where('sheikh_id', $sheikhId)
        ->where(DB::raw('`session_date`'), '>=', $monthStart)->count();
    $reviewThisWeek  = ReviewLog::where('sheikh_id', $sheikhId)
        ->where(DB::raw('`session_date`'), '>=', $weekStart)->count();
    $reviewThisMonth = ReviewLog::where('sheikh_id', $sheikhId)
        ->where(DB::raw('`session_date`'), '>=', $monthStart)->count();

    // ── Build hifz query ──────────────────────────────────────────────────
    $hifzQuery = HifzLog::where('sheikh_id', $sheikhId)
        ->with(['student', 'course'])
        ->when($search, function ($q) use ($search) {
            $q->whereHas('student', fn($s) => $s->where('name', 'like', "%$search%"))
              ->orWhere('start_surah', 'like', "%$search%");
        })
        ->when($fromDate, fn($q) => $q->where(DB::raw('`session_date`'), '>=', $fromDate))
        ->when($toDate,   fn($q) => $q->where(DB::raw('`session_date`'), '<=', $toDate));

    // ── Build review query ────────────────────────────────────────────────
    $reviewQuery = ReviewLog::where('sheikh_id', $sheikhId)
        ->with(['student'])
        ->when($search, function ($q) use ($search) {
            $q->whereHas('student', fn($s) => $s->where('name', 'like', "%$search%"))
              ->orWhere('start_surah', 'like', "%$search%");
        })
        ->when($fromDate, fn($q) => $q->where(DB::raw('`session_date`'), '>=', $fromDate))
        ->when($toDate,   fn($q) => $q->where(DB::raw('`session_date`'), '<=', $toDate));

    // ── Merge based on type filter ────────────────────────────────────────
    $hifzLogs   = ($type === 'review') ? collect() : $hifzQuery->get();
    $reviewLogs = ($type === 'hifz')   ? collect() : $reviewQuery->get();

    // Normalise to a unified shape
    $hifzMapped = $hifzLogs
        ->filter(fn($l) => $l->student !== null)
        ->map(fn($l) => [
            'id'           => $l->id,
            'type'         => 'hifz',
            'student_id'   => $l->student->id,
            'student_name' => $l->student->name,
            'surah_name'   => $l->start_sura,
            'from_ayah'    => $l->start_ayah,
            'to_ayah'      => $l->end_ayah,
            'date'         => $l->date instanceof \Carbon\Carbon
                                ? $l->date->toDateString()
                                : $l->date,
            'course_id'    => $l->course?->id   ?? 0,
            'course_name'  => $l->course?->name ?? 'غير محدد',
            'created_at'   => $l->created_at,
        ]);

    $reviewMapped = $reviewLogs
        ->filter(fn($l) => $l->student !== null)
        ->map(fn($l) => [
            'id'           => $l->id,
            'type'         => 'review',
            'student_id'   => $l->student->id,
            'student_name' => $l->student->name,
            'surah_name'   => $l->start_surah,
            'from_ayah'    => $l->start_ayah,
            'to_ayah'      => $l->end_ayah,
            'date'         => $l->date instanceof \Carbon\Carbon
                                ? $l->date->toDateString()
                                : $l->date,
            'course_id'    => 0,
            'course_name'  => $l->course?->name ??'مراجعة عامة',
            'created_at'   => $l->created_at,
        ]);

    // Merge → sort by date desc → manual paginate
    $allLogs = $hifzMapped->merge($reviewMapped)
        ->sortByDesc('date')
        ->sortByDesc('created_at')
        ->values();

    $total       = $allLogs->count();
    $currentPage = (int) $request->get('page', 1);
    $paginated   = $allLogs->forPage($currentPage, $perPage)->values();

    return response()->json([
        'success' => true,
        'data'    => [
            'summary' => [
                'hifz_this_week'    => $hifzThisWeek,
                'hifz_this_month'   => $hifzThisMonth,
                'review_this_week'  => $reviewThisWeek,
                'review_this_month' => $reviewThisMonth,
                'total_this_week'   => $hifzThisWeek  + $reviewThisWeek,
                'total_this_month'  => $hifzThisMonth + $reviewThisMonth,
            ],
            'logs'       => $paginated,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $currentPage,
                'last_page'    => (int) ceil($total / $perPage),
                'has_more'     => $currentPage < ceil($total / $perPage),
            ],
        ],
    ]);
}


}

