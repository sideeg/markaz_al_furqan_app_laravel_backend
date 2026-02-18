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
        Schema::table('english_on_users', function (Blueprint $table) {
            \DB::statement("ALTER TABLE users MODIFY COLUMN gender ENUM('male', 'female') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
    
            // Convert any existing Arabic values
            \DB::statement("UPDATE users SET gender = 'male' WHERE gender = 'ذكر'");
            \DB::statement("UPDATE users SET gender = 'female' WHERE gender = 'أنثي'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('english_on_users', function (Blueprint $table) {
           \DB::statement("UPDATE users SET gender = 'ذكر' WHERE gender = 'male'");
            \DB::statement("UPDATE users SET gender = 'أنثي' WHERE gender = 'female'");
            \DB::statement("ALTER TABLE users MODIFY COLUMN gender ENUM('ذكر', 'أنثي') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
        });
    }
};
