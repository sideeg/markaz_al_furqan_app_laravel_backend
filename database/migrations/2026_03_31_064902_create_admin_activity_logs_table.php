<?php
/**
 * MIGRATION: Create Admin Activity Logs Table
 * 
 * File: database/migrations/YYYY_MM_DD_create_admin_activity_logs_table.php
 * 
 * Tracks all admin actions for audit trail
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Who did it
            $table->foreignId('admin_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // What action was performed
            $table->enum('action', [
                'create',           // Created something
                'update',           // Updated something
                'delete',           // Deleted something
                'send_notification',// Sent a notification
                'approve_enrollment',// Approved student enrollment
                'reject_enrollment', // Rejected student enrollment
                'assign_sheikh',    // Assigned sheikh to course
            ]);
            
            // What type of model was affected
            $table->string('model_type');  // e.g., Course, Mosque, Group, Notification
            
            // What specific record was affected
            $table->unsignedBigInteger('model_id');
            
            // Display name of the affected item
            $table->string('model_name');  // e.g., "دورة الفرقان"
            
            // Human readable description
            $table->text('description');
            
            // Before values (for updates)
            $table->json('old_data')->nullable();
            
            // After values (for updates/creates)
            $table->json('new_data')->nullable();
            
            // IP address of the request
            $table->string('ip_address')->nullable();
            
            // User agent for browser info
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('admin_id');
            $table->index('action');
            $table->index('model_type');
            $table->index('created_at');
            $table->index(['admin_id', 'created_at']);  // For admin activity filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};