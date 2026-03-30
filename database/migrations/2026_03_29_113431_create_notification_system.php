<?php
/**
 * MIGRATION: Alter existing notifications table for v3
 * 
 * File: database/migrations/YYYY_MM_DD_alter_notifications_for_v3.php
 * 
 * This ALTER migration upgrades your existing notifications table
 * to support the new v3 requirements without losing data
 * 
 * What it does:
 * 1. Changes type enum to support new notification types
 * 2. Adds target field (students, teachers, both)
 * 3. Adds is_active field
 * 4. Adds sent_at timestamp
 * 5. Keeps all existing data intact
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only modify if notifications table exists
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            
            // 1. Add 'target' field for selective notifications ✅
            if (!Schema::hasColumn('notifications', 'target')) {
                $table->enum('target', ['students', 'teachers', 'both'])
                      ->default('both')
                      ->after('type');
            }

            // 2. Add 'is_active' field ✅
            if (!Schema::hasColumn('notifications', 'is_active')) {
                $table->boolean('is_active')
                      ->default(true)
                      ->after('target');
            }

            // 3. Add 'sent_at' timestamp ✅
            if (!Schema::hasColumn('notifications', 'sent_at')) {
                $table->timestamp('sent_at')
                      ->nullable()
                      ->after('is_active');
            }
        });

        // 4. Change type enum values to support v3 notification types
        // NOTE: MySQL doesn't allow direct enum modification in all versions
        // So we use raw SQL statement
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE notifications 
                MODIFY COLUMN type ENUM(
                    'enrollment',
                    'course_start', 
                    'course_end',
                    'custom_broadcast',
                    'info',
                    'success',
                    'warning',
                    'error'
                ) DEFAULT 'custom_broadcast'
            ");
        }
        // For other databases (SQLite, PostgreSQL), enum columns might need different handling
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            // Remove new columns in reverse order
            if (Schema::hasColumn('notifications', 'sent_at')) {
                $table->dropColumn('sent_at');
            }
            
            if (Schema::hasColumn('notifications', 'is_active')) {
                $table->dropColumn('is_active');
            }
            
            if (Schema::hasColumn('notifications', 'target')) {
                $table->dropColumn('target');
            }
        });

        // Revert type enum to original values
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE notifications 
                MODIFY COLUMN type ENUM(
                    'info',
                    'success',
                    'warning',
                    'error'
                ) DEFAULT 'info'
            ");
        }
    }
};