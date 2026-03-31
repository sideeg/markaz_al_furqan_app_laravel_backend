<?php
/**
 * COMMAND: Clean up old activity logs
 * 
 * File: app/Console/Commands/CleanupActivityLogs.php
 * 
 * Run this monthly to delete logs older than 1 year
 * Schedule: app/Console/Kernel.php
 */

namespace App\Console\Commands;

use App\Models\AdminActivityLog;
use Illuminate\Console\Command;

class CleanupActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-logs:cleanup {--days=365 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete activity logs older than specified days (default: 1 year)';

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("🧹 Cleaning up activity logs older than {$days} days ({$cutoffDate->format('Y-m-d')})...");

        // Count logs before deletion
        $beforeCount = AdminActivityLog::count();

        // Delete old logs
        $deletedCount = AdminActivityLog::where('created_at', '<', $cutoffDate)->delete();

        // Count logs after deletion
        $afterCount = AdminActivityLog::count();

        $this->info("✅ Cleanup complete!");
        $this->info("   Total logs before: {$beforeCount}");
        $this->info("   Logs deleted: {$deletedCount}");
        $this->info("   Total logs after: {$afterCount}");

        return Command::SUCCESS;
    }
}