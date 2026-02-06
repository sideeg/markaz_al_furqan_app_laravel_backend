<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CourseService;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Get all courses with filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'sometimes|in:online,open,closed',
                'search' => 'sometimes|string|max:255',
                'mosque_id' => 'sometimes|integer|exists:mosques,id',
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            $query = Course::with(['mosque', 'creator'])
                          ->active()
                          ->latest();

            // Apply filters
            if ($request->has('type')) {
                $query->ofType($request->type);
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->has('mosque_id')) {
                $query->byMosque($request->mosque_id);
            }

            $perPage = $request->get('per_page', 20);
            $courses = $query->paginate($perPage);

            // Add enrollment status for authenticated user
            if ($request->user()) {
                $courses->getCollection()->transform(function ($course) use ($request) {
                    $course->enrollment_status = $course->getUserEnrollmentStatus($request->user()->id);
                    return $course;
                });
            }
            Log::info('Courses retrieved successfully', [
                'total' => $courses->total(),
                'current_page' => $courses->currentPage(),
                'data' => $courses->toArray(),
            ]);
            return response()->json([
                'success' => true,
                'data' => $courses->items(),
                'meta' => [
                    'pagination' => [
                        'current_page' => $courses->currentPage(),
                        'total_pages' => $courses->lastPage(),
                        'per_page' => $courses->perPage(),
                        'total' => $courses->total(),
                        'has_more' => $courses->hasMorePages(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل الدورات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get course details.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        try {
            $course->load(['mosque', 'creator', 'sheikhs', 'groups']);

            // Add enrollment status for authenticated user
            if ($request->user()) {
                $course->enrollment_status = $course->getUserEnrollmentStatus($request->user()->id);
            }

            return response()->json([
                'success' => true,
                'data' => $course,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل تفاصيل الدورة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enroll in a course.
     */
    public function enroll(Request $request, Course $course): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is already enrolled
            if ($course->isUserEnrolled($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'أنت مسجل في هذه الدورة بالفعل',
                ], 409);
            }

            // Check if course can accept enrollments
            if (!$course->can_enroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'التسجيل في هذه الدورة غير متاح حالياً',
                ], 422);
            }

            // Create enrollment
            $enrollment = $this->courseService->enrollStudent($course->id, $user->id, $request->notes);

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلب التسجيل بنجاح. سيتم إشعارك عند الموافقة',
                'data' => [
                    'enrollment_id' => $enrollment->id,
                    'status' => $enrollment->status,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Course enrollment error', [
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التسجيل في الدورة',
                'error' => $e->getMessage(),
               
            ], 500);
        }
    }

    /**
     * Withdraw from a course.
     */
    public function withdraw(Request $request, Course $course): JsonResponse
    {
        try {
            $user = $request->user();

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                                        ->where('student_id', $user->id)
                                        ->whereIn('status', ['pending', 'approved'])
                                        ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على تسجيل في هذه الدورة',
                ], 404);
            }

            $this->courseService->withdrawStudent($course, $user);

            return response()->json([
                'success' => true,
                'message' => 'تم الانسحاب من الدورة بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الانسحاب من الدورة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's enrolled courses.
     */
    public function myCourses(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $courses = $user->enrolledCourses()
                           ->with(['mosque', 'sheikhs'])
                           ->get()
                           ->map(function ($course) {
                               $course->enrollment_status = $course->pivot->status;
                               $course->enrolled_at = $course->pivot->enrolled_at;
                               $course->approved_at = $course->pivot->approved_at;
                               return $course;
                           });

            return response()->json([
                'success' => true,
                'data' => $courses,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل دوراتي',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get course progress for student.
     */
    public function progress(Request $request, Course $course): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is enrolled and approved
            $enrollment = CourseEnrollment::where('course_id', $course->id)
                                        ->where('student_id', $user->id)
                                        ->where('status', 'approved')
                                        ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على تسجيل مقبول في هذه الدورة',
                ], 404);
            }

            $progress = $this->courseService->getStudentProgress($course, $user);

            return response()->json([
                'success' => true,
                'data' => $progress,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل التقدم',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get course evaluations for student.
     */
    public function evaluations(Request $request, Course $course): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is enrolled and approved
            $enrollment = CourseEnrollment::where('course_id', $course->id)
                                        ->where('student_id', $user->id)
                                        ->where('status', 'approved')
                                        ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على تسجيل مقبول في هذه الدورة',
                ], 404);
            }

            $evaluations = $this->courseService->getStudentEvaluations($course, $user);

            return response()->json([
                'success' => true,
                'data' => $evaluations,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل التقييمات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get featured courses.
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $courses = Course::with(['mosque', 'creator'])
                           ->active()
                           ->openRegistration()
                           ->withAvailableSlots()
                           ->latest()
                           ->limit(6)
                           ->get();

            // Add enrollment status for authenticated user
            if ($request->user()) {
                $courses->transform(function ($course) use ($request) {
                    $course->enrollment_status = $course->getUserEnrollmentStatus($request->user()->id);
                    return $course;
                });
            }

            return response()->json([
                'success' => true,
                'data' => $courses,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل الدورات المميزة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get course statistics.
     */
    public function statistics(Course $course): JsonResponse
    {
        try {
            $stats = $course->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل إحصائيات الدورة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

