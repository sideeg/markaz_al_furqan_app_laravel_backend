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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['online', 'open', 'closed'])->default('open');
            $table->foreignId('mosque_id')->nullable()->constrained('mosques')->onDelete('set null');
            $table->string('image_path')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('max_students')->default(50);
            $table->integer('current_students')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_registration_open')->default(true);
            $table->text('requirements')->nullable();
            $table->text('schedule_details')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'is_active', 'is_registration_open']);
            $table->index(['mosque_id', 'is_active']);
            $table->index('created_by');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

