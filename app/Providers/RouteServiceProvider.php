<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * مسار الصفحة الرئيسية (بعد تسجيل الدخول)
     */
    public const HOME = '/dashboard';

    /**
     * تسجيل الروتات
     */
    public function boot(): void
    {
        \Log::info('RouteServiceProvider booting');
        $this->configureRateLimiting();

        $this->routes(function () {
            \Log::info('Loading API routes');
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('console')
                ->group(base_path('routes/console.php'));
        });
    }
}
