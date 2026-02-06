<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\HifzController;
use App\Http\Controllers\Api\SheikhController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/sheikh/login', [SheikhController::class, 'login']);
    
    // Public course information
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/featured', [CourseController::class, 'featured']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::get('/courses/{course}/stats', [CourseController::class, 'statistics']);

    // Authentication
    Route::post('/sheikh/login', 'App\Http\Controllers\Api\SheikhController@login');

    // Authenticated Routes
        // Dashboard
       
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    
    // Course enrollment (Student)
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll']);
    Route::delete('/courses/{course}/withdraw', [CourseController::class, 'withdraw']);
    Route::get('/my-courses', [CourseController::class, 'myCourses']);
    Route::get('/my-progress/{course}', [CourseController::class, 'progress']);
    Route::get('/my-evaluations/{course}', [CourseController::class, 'evaluations']);
    
    // Hifz and Review Logs (Student)
    Route::prefix('hifz')->group(function () {
        Route::get('/my-logs', [HifzController::class, 'myLogs']);
        Route::get('/my-logs/{course}', [HifzController::class, 'myLogsByCourse']);
        Route::get('/my-progress', [HifzController::class, 'myProgress']);
        Route::get('/my-statistics', [HifzController::class, 'myStatistics']);
    });
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'delete']);
    });
    

// Sheikh routes
Route::middleware('role:sheikh')->prefix('sheikh')->group(function () {

        Route::post('/logout', [SheikhController::class, 'logout']);

    // Course management
    Route::get('/courses', [SheikhController::class, 'myCourses']); // ✅ المسار المطلوب
    Route::get('/courses/{course}/groups', [SheikhController::class, 'courseGroups']);
    Route::get('/courses/{course}/groups/{group}/students', [SheikhController::class, 'groupStudents']);
    
    // إضافة مسار للوصول للطلاب عبر group_id مباشرة
    Route::get('/groups/{group}/students', [SheikhController::class, 'groupStudents']);
    
    // Student management
    Route::get('/students', [SheikhController::class, 'myStudents']);
    Route::get('/students/{student}', [SheikhController::class, 'getStudent']); // إضافة هذا
    Route::get('/students/{student}/progress', [SheikhController::class, 'studentProgress']);
    Route::get('/students/{student}/evaluations', [SheikhController::class, 'studentEvaluations']);
    Route::get('/students/{student}/hifz-logs', [HifzController::class, 'studentHifzLogs']); // إضافة هذا
    Route::get('/students/{student}/review-logs', [HifzController::class, 'studentReviewLogs']); // إضافة هذا
   
    // Hifz logging
    Route::post('/hifz-logs', [HifzController::class, 'store']);
    Route::put('/hifz-logs/{hifzLog}', [HifzController::class, 'update']);
    Route::delete('/hifz-logs/{hifzLog}', [HifzController::class, 'destroy']);
    Route::get('/hifz-logs', [HifzController::class, 'index']);
    Route::get('/hifz-logs/{hifzLog}', [HifzController::class, 'show']);
   
    // Review logging
    Route::post('/review-logs', [HifzController::class, 'storeReview']);
    Route::put('/review-logs/{reviewLog}', [HifzController::class, 'updateReview']);
    Route::delete('/review-logs/{reviewLog}', [HifzController::class, 'destroyReview']);
    Route::get('/review-logs', [HifzController::class, 'reviewIndex']);
    
    // Statistics and reports
    Route::get('/dashboard', [SheikhController::class, 'getDashboard']);
    Route::get('/statistics', [SheikhController::class, 'statistics']);
    Route::get('/reports/students', [SheikhController::class, 'studentsReport']);
    Route::get('/reports/progress', [SheikhController::class, 'progressReport']);

    Route::put('/profile', [SheikhController::class, 'updateProfile']);
    Route::get('/profile', [SheikhController::class, 'getProfile']);
    // Notifications
    Route::post('/notifications/send', [NotificationController::class, 'sendToStudents']);
});
});
// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});

