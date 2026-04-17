<?php
// Path: database/migrations/2026_04_04_064911_create_notification_recipients_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to notifications table
            $table->foreignId('notification_id')
                  ->constrained('notifications')
                  ->onDelete('cascade');
            
            // Foreign key to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // When the user read this notification (NULL = unread)
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Ensure each user gets notified only once per notification
            $table->unique(['notification_id', 'user_id']);
            
            // Index for querying user's unread notifications (important for performance)
            $table->index(['user_id', 'read_at']);
            
            // Index for getting unread count
            $table->index(['notification_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};