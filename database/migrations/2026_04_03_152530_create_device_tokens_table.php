<?php
// Path: database/migrations/2026_04_03_152530_create_device_tokens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create if doesn't exist already
        if (!Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                
                // Foreign key to users table
                $table->foreignId('user_id')
                      ->constrained('users')
                      ->onDelete('cascade');
                
                // The FCM token from Firebase Cloud Messaging
                $table->string('fcm_token')->unique();
                
                // Device type for analytics (ios, android, web)
                $table->string('device_type')->nullable();
                
                // Device name/model for tracking which devices
                $table->string('device_name')->nullable();
                
                $table->timestamps();
                
                // Index for finding tokens by user (for targeted notifications)
                $table->index('user_id');
                
                // Index for finding fcm_token during cleanup
                $table->index('fcm_token');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};