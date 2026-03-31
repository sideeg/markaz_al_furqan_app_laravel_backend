<?php
/**
 * CONSOLE KERNEL - Schedule Activity Log Cleanup
 * 
 * File: app/Console/Kernel.php
 * 
 * Schedule the cleanup command to run monthly
 */

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Delete activity logs older than 1 year
        // Run on the first day of each month at 2:00 AM
        $schedule->command('activity-logs:cleanup --days=365')
                 ->monthlyOn(1, '02:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     // Optional: Log successful cleanup
                     \Log::info('Activity logs cleanup completed successfully');
                 })
                 ->onFailure(function () {
                     // Optional: Log failed cleanup
                     \Log::error('Activity logs cleanup failed');
                 });

        // ✅ Delete activity logs older than 2 years
        $schedule->command('activity-logs:cleanup --days=730')
                 ->monthlyOn(15, '02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}