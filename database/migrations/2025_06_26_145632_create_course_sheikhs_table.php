<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('course_sheikhs', function (Blueprint $table) {
        $table->id();

        $table->foreignId('sheikh_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
        $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');

        $table->string('role')->default('sheikh'); // أو 'supervisor' مثلاً
        $table->timestamp('assigned_at')->nullable();
        $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_sheikhs');
    }
};
