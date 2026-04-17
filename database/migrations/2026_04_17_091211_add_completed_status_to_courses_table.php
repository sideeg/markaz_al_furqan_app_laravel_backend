<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Adding a boolean and a timestamp for better history tracking
            $table->boolean('is_completed')->default(false)->after('is_registration_open');
            $table->timestamp('completed_at')->nullable()->after('is_completed');
            
            $table->index('is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['is_completed']);
            $table->dropColumn(['is_completed', 'completed_at']);
        });
    }
};