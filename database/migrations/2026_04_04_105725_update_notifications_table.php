<?php
// Path: database/migrations/2024_04_XX_000001_update_notifications_table.php
// Replace XX with today's date (e.g., 2024_04_04)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE notifications 
                MODIFY COLUMN target ENUM('individual', 'students', 'teachers', 'both') DEFAULT 'individual'
            ");

            DB::statement("
                ALTER TABLE notifications 
                MODIFY COLUMN type ENUM(
                    'enrollment',
                    'course_start', 
                    'course_end',
                    'new_student',
                    'custom_broadcast',
                    'info',
                    'success',
                    'warning',
                    'error'
                ) DEFAULT 'custom_broadcast'
            ");
        }

        // 2. Add indexes for performance (since the first migration didn't add them)
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('sent_at');
            $table->index('target');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['sent_at']);
            $table->dropIndex(['target']);
        });

        // Revert enums back to the previous state
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN target ENUM('students', 'teachers', 'both') DEFAULT 'both'");
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('enrollment', 'course_start', 'course_end', 'custom_broadcast', 'info', 'success', 'warning', 'error') DEFAULT 'custom_broadcast'");
        }
    
    }
};