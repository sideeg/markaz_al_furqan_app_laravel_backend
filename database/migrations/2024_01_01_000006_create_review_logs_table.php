<?php

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
        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sheikh_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
            $table->text('start_surah');
            $table->text('end_surah');
            $table->integer('start_ayah');
            $table->integer('end_ayah');
            $table->enum('evaluation', ['excellent', 'very_good', 'good', 'needs_improvement', 'poor']);
            $table->text('notes')->nullable();
            $table->date('session_date');
            $table->time('session_time')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'course_id', 'session_date']);
            $table->index(['sheikh_id', 'session_date']);
            $table->index(['course_id', 'session_date']);
            $table->index(['group_id', 'session_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_logs');
    }
};

