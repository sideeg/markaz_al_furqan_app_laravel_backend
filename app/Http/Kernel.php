<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     */
    protected $middleware = [
        // Security & performance
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Route middleware.
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Custom role middleware from Spatie
        'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
    ];

        /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ─────────────────────────────────────────────────────────────
        // Course Notifications (run daily at midnight)
        // ─────────────────────────────────────────────────────────────
 
        $schedule->daily(function () {
            try {
                $notificationService = app(NotificationService::class);
                $today = now()->startOfDay();
 
                // 1. Find courses starting today
                $coursesStartingToday = Course::whereDate('start_date', $today)
                    ->where('status', '!=', 'completed')
                    ->get();
 
                foreach ($coursesStartingToday as $course) {
                    // Notify all enrolled students
                    $notificationService->notifyCourseStarting(
                        $course->id,
                        $course->name,
                        $course->start_time ?? '09:00'
                    );
 
                    Log::info("Sent course start notification for: {$course->name}");
                }
 
                // 2. Find courses ending today
                $coursesEndingToday = Course::whereDate('end_date', $today)
                    ->where('status', '!=', 'completed')
                    ->get();
 
                foreach ($coursesEndingToday as $course) {
                    // Notify all enrolled students
                    $notificationService->notifyCourseCompleted($course->id);
 
                    // Mark course as completed/done
                    $course->update(['status' => 'completed']);
 
                    Log::info("Sent course end notification for: {$course->name}");
                    Log::info("Marked course as completed: {$course->name}");
                }
            } catch (\Exception $e) {
                Log::error('Scheduled notification job failed: ' . $e->getMessage());
            }
        })->name('send-course-notifications');
 
        // ─────────────────────────────────────────────────────────────
        // Cleanup: Delete old notifications (older than 90 days)
        // ─────────────────────────────────────────────────────────────
        $schedule->daily(function () {
            try {
                \App\Models\Notification::where('sent_at', '<', now()->subDays(90))
                    ->delete();
 
                Log::info('Cleaned up old notifications');
            } catch (\Exception $e) {
                Log::error('Notification cleanup failed: ' . $e->getMessage());
            }
        })->name('cleanup-old-notifications');
 
        // ─────────────────────────────────────────────────────────────
        // Cleanup: Remove orphaned device tokens (users deleted)
        // ─────────────────────────────────────────────────────────────
        $schedule->weekly(function () {
            try {
                \App\Models\DeviceToken::whereDoesntHave('user')->delete();
 
                Log::info('Cleaned up orphaned device tokens');
            } catch (\Exception $e) {
                Log::error('Device token cleanup failed: ' . $e->getMessage());
            }
        })->name('cleanup-orphaned-device-tokens');
    }
 
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
 
        require base_path('routes/console.php');
    }
}
