<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Course;
use App\Models\Group;
use App\Models\Mosque;
use App\Models\Notification;
use App\Models\Enrollment;
use App\Observers\CourseObserver;
use App\Observers\GroupObserver;
use App\Observers\MosqueObserver;
use App\Observers\NotificationObserver;
use App\Observers\EnrollmentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        // Link the models to their observers
        Course::observe(CourseObserver::class);
        Group::observe(GroupObserver::class);
        Mosque::observe(MosqueObserver::class);
        Notification::observe(NotificationObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
    }
}
