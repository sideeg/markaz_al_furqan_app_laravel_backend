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

class SheikhController extends Controller
{
    public function login(Request $request)
    {
        try {
            Log::info('Sheikh login request data: ', $request->all());
            $user = User::where('email', $request->email)->first();
            Log::info('User found: ', ['user_password' => $user->password]);
            // if (!$user || !Hash::check($request->password, $user->password)) {
            //     throw ValidationException::withMessages([
            //         'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة'],
            //     ]);
            // }

            // Check if user is active
             Log::info('User found: ', ['user_id' => $user->id]);
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
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
    
    Log::info('Fetching courses for sheikh: ', [
        'sheikh_id' => auth()->id(),
        'sheikh_name' => auth()->user()->name,
        'groups_count' => $groups->count()
    ]);

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

    Log::info('Course data prepared: ', ['courses_count' => count($coursesData)]);

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
            Log::info('No groups found for course', [
                'course_id' => $course_id,
                'sheikh_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'لا توجد مجموعات لهذه الدورة',
            ], 404);
        }
        Log::info('Fetching groups for course: ', [
            'course_id' => $course_id,
            'sheikh_id' => auth()->id(),
            'groups_count' => $groups->count()
        ]);
        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

   // Backend Controller Method - Improved Version
public function myStudents()
{
    $sheikh_id = auth()->id();
    
    // Get groups with their related data
    $groups = Group::where('sheikh_id', $sheikh_id)
        ->with(['students', 'course'])
        ->get();
    
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
        }
    }
    
    // Get courses for additional info if needed
    $courses = Course::whereIn('id', $groups->pluck('course_id'))->get();
    
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
            Log::info('Sheikh logged out successfully', ['sheikh_id' => $user->id]);
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح',
            ]);
        }

        Log::warning('Logout attempt without authenticated user');
        return response()->json([
            'success' => false,
            'message' => 'لم يتم العثور على مستخدم مسجل الدخول',
        ], 401);
    }

    public function updateProfile(Request $request)
    {
        
        Log::info('Sheikh profile update request data: ', $request->all());
        $user = auth()->user();
        // Handle password update separately
        if ($request->has('current_password')) {
            // Validate password fields
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed:new_password_confirmation',
                'new_password_confirmation' => 'required|string',
            ]);
            Log::info("hash check",[Hash::check($request->current_password, $user->password)]);
            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                Log::info("it's here");
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور الحالية غير صحيحة',
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            Log::info('Sheikh password updated successfully', ['sheikh_id' => $user->id]);

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

        Log::info('Sheikh profile updated successfully', ['sheikh_id' => $user->id]);

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
        
        // Get counts using your actual models and relationships
        
        // Total Students: Get unique students from groups where this sheikh is assigned
        $totalStudents = User::role('student')
            ->whereHas('groups', function($q) use ($sheikhId) {
                $q->where('sheikh_id', $sheikhId);
            })
            ->distinct()
            ->count();
        
        // Total Courses: Get courses that have groups where this sheikh is assigned
        $totalCourses = Course::whereHas('groups', function($q) use ($sheikhId) {
            $q->where('sheikh_id', $sheikhId);
        })
        ->distinct()
        ->count();
        
        // Total Groups: Get groups directly assigned to this sheikh
        $totalGroups = Group::where('sheikh_id', $sheikhId)->count();
        
        // Get recent hifz logs (last 5) using Eloquent with eager loading
        $recentHifzLogs = HifzLog::where('sheikh_id', $sheikhId)
            ->with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'surah_name' => $log->start_sura, // or however you store surah name
                    'from_ayah' => $log->start_ayah,
                    'to_ayah' => $log->end_ayah,
                    'date' => $log->date,
                    'created_at' => $log->created_at,
                    'student' => [
                        'id' => $log->student->id,
                        'name' => $log->student->name,
                    ],
                    'course' => [
                        'id' => $log->course->id,
                        'name' => $log->course->name,
                    ],
                ];
            });
        
        // Get recent review logs (last 5) using Eloquent with eager loading
        $recentReviewLogs = ReviewLog::where('sheikh_id', $sheikhId)
            ->with(['student'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'surah_name' => $log->surah,
                    'from_ayah' => $log->start_ayah,
                    'to_ayah' => $log->end_ayah,
                    'date' => $log->date,
                    'created_at' => $log->created_at,
                    'student' => [
                        'id' => $log->student->id,
                        'name' => $log->student->name,
                    ],
                    'course' => [
                        'id' => 0, // ReviewLog doesn't have course_id in your model
                        'name' => 'مراجعة عامة',
                    ],
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_students' => $totalStudents,
                    'total_courses' => $totalCourses,
                    'total_groups' => $totalGroups,
                ],
                'recent_hifz_logs' => $recentHifzLogs,
                'recent_review_logs' => $recentReviewLogs,
            ]
        ]);
    }
}

