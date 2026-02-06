<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SheikhController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\MosqueController;
use App\Http\Controllers\MosqueManagementController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\HifzLogController;
use App\Http\Controllers\Admin\ReviewLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login if not authenticated, otherwise to dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // Role-specific dashboard routes
    Route::get('/student/dashboard', [AuthController::class, 'studentDashboard'])
        ->name('student.dashboard')
        ->middleware('role:student');
    
    Route::get('/sheikh/dashboard', [AuthController::class, 'sheikhDashboard'])
        ->name('sheikh.dashboard')
        ->middleware('role:sheikh');
    
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])
        ->name('admin.dashboard')
        ->middleware('role:admin');
    
    Route::get('/supervisor/dashboard', [AuthController::class, 'supervisorDashboard'])
        ->name('supervisor.dashboard')
        ->middleware('role:supervisor');

    // ✅ NEW ADMIN ACTION ROUTES
    // Route::post('/admin/courses', [CourseController::class, 'store'])->name('admin.courses.store');
    // Route::post('/admin/mosques', [MosqueManagementController::class, 'store'])->name('admin.mosques.store');
    // Route::post('/admin/sheikhs', [SheikhController::class, 'store'])->name('admin.sheikhs.store');
Route::post('/admin/admins', [AdminController::class, 'store'])->name('admin.admins.store');
Route::post('/admin/notifications', [NotificationController::class, 'store'])->name('admin.notifications.store');
Route::resource('mosques', MosqueManagementController::class);

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('hifz_logs', HifzLogController::class)->only([
        'index', 'show', 'destroy'
    ]);
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('review_logs', ReviewLogController::class)->only([
        'index', 'show', 'destroy'
    ]);
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('admin.notifications.store');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('admin.notifications.show');
    Route::get('/notifications/{notification}/edit', [NotificationController::class, 'edit'])->name('admin.notifications.edit');
    Route::put('/notifications/{notification}', [NotificationController::class, 'update'])->name('admin.notifications.update');
});
Route::prefix('students')->middleware(['auth', 'role:admin|supervisor'])->group(function () {
    Route::get('/', [StudentController::class, 'index'])->name('admin.students.index');
    Route::get('/create', [StudentController::class, 'create'])->name('admin.students.create');
    Route::post('/', [StudentController::class, 'store'])->name('admin.students.store');
    Route::get('/{student}', [StudentController::class, 'show'])->name('admin.students.show');
    Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('admin.students.edit');
    Route::put('/{student}', [StudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/{student}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::patch('/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('admin.students.toggle-status');
});

// Sheikh management routes
Route::prefix('admin')->group(function() {
    Route::resource('sheikhs', \App\Http\Controllers\Admin\SheikhController::class) ->names([
        'index' => 'admin.sheikhs.index',
        'create' => 'admin.sheikhs.create',
        'store' => 'admin.sheikhs.store',
        'show' => 'admin.sheikhs.show',
        'edit' => 'admin.sheikhs.edit',
        'update' => 'admin.sheikhs.update',
        'destroy' => 'admin.sheikhs.destroy'
    ]);

    Route::get('sheikhs/{sheikh}/schedule', [\App\Http\Controllers\Admin\SheikhController::class, 'schedule'])
        ->name('admin.sheikhs.schedule');

    Route::patch('sheikhs/{sheikh}/toggle-status', [\App\Http\Controllers\Admin\SheikhController::class, 'toggleStatus'])
        ->name('admin.sheikhs.toggle-status');

    // Admin management routes
    Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class)
        ->names([
            'index' => 'admin.admins.index',
            'create' => 'admin.admins.create',
            'store' => 'admin.admins.store',
            'show' => 'admin.admins.show',
            'edit' => 'admin.admins.edit',
            'update' => 'admin.admins.update',
            'destroy' => 'admin.admins.destroy'
        ]);

    Route::get('admins/{admin}/activity', [\App\Http\Controllers\Admin\AdminController::class, 'activity'])
        ->name('admin.admins.activity');

    Route::patch('admins/{admin}/toggle-status', [\App\Http\Controllers\Admin\AdminController::class, 'toggleStatus'])
        ->name('admin.admins.toggle-status');

    // Status toggle route
    Route::patch('/mosques/{mosque}/toggle-status', [MosqueManagementController::class, 'toggleStatus'])
        ->name('mosques.toggle-status');

    // Course resource routes
    Route::resource('admin/courses', \App\Http\Controllers\Admin\CourseController::class)->names([
        'index' => 'admin.courses.index',
        'create' => 'admin.courses.create',
        'store' => 'admin.courses.store',
        'show' => 'admin.courses.show',
        'edit' => 'admin.courses.edit',
        'update' => 'admin.courses.update',
        'destroy' => 'admin.courses.destroy'
    ]);

    // Enrollment management routes
    Route::prefix('admin')->group(function() {
    Route::resource('enrollments', \App\Http\Controllers\Admin\EnrollmentController::class)
        ->only(['index', 'show', 'destroy'])
        ->names([
            'index' => 'admin.enrollments.index',
            'show' => 'admin.enrollments.show',
            'destroy' => 'admin.enrollments.destroy'
        ]);
    
    Route::post('enrollments/{enrollment}/approve', [\App\Http\Controllers\Admin\EnrollmentController::class, 'approve'])
        ->name('admin.enrollments.approve');
    
    Route::post('enrollments/{enrollment}/reject', [\App\Http\Controllers\Admin\EnrollmentController::class, 'reject'])
        ->name('admin.enrollments.reject');

        // Group management routes
Route::prefix('admin/courses/{course}')->group(function() {
    Route::resource('groups', \App\Http\Controllers\Admin\GroupController::class)
        ->names([
            'index' => 'admin.courses.groups.index',
            'create' => 'admin.courses.groups.create',
            'store' => 'admin.courses.groups.store',
            'show' => 'admin.courses.groups.show',
            'edit' => 'admin.courses.groups.edit',
            'update' => 'admin.courses.groups.update',
            'destroy' => 'admin.courses.groups.destroy'
        ]);
    
    // Additional group management routes
    Route::post('groups/{group}/add-student', [\App\Http\Controllers\Admin\GroupController::class, 'addStudent'])
        ->name('admin.courses.groups.add-student');
    
    Route::delete('groups/{group}/students/{student}', [\App\Http\Controllers\Admin\GroupController::class, 'removeStudent'])
        ->name('admin.courses.groups.remove-student');
    
    Route::patch('groups/{group}/toggle-status', [\App\Http\Controllers\Admin\GroupController::class, 'toggleStatus'])
        ->name('admin.courses.groups.toggle-status');
});
});



    // Course status toggle route
    Route::patch('/admin/courses/{course}/toggle-status', [\App\Http\Controllers\Admin\CourseController::class, 'toggleStatus'])
        ->name('admin.courses.toggle-status');
});
});

// Fallback route
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
// Route to handle 404 errors
Route::get('/404', function () {
    return response()->view('errors.404', [], 404);
})->name('404');    
