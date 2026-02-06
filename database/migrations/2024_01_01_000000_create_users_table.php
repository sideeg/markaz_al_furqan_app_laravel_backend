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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('phone')->nullable();
        $table->string('national_id')->nullable();
        $table->enum('qiraat', [
            'شعبة عن عاصم الكوفي',
            'حفص عن عاصم الكوفي',
            'خلف عن حمزة الكوفي',
            'خلاد عن حمزة الكوفي',
            'قالون عن نافع المدني',
            'ورش عن نافع المدني',
            'البزي عن ابن كثير المكي',
            'قنبل عن ابن كثير المكي',
            'الدوري عن أبي عمرو البصري',
            'السوسي عن أبي عمرو البصري',
            'هشام عن ابن عامر الشامي',
            'ابن ذكوان عن ابن عامر الشامي',
            'أبو الحارث عن الكسائي الكوفي',
            'الدوري عن الكسائي الكوفي',
            'ابن وردان عن أبي جعفر المدني',
            'ابن جماز عن أبي جعفر المدني',
            'رويس عن يعقوب الحضرمي',
            'روح عن يعقوب الحضرمي',
            'إسحاق عن خلف العاشر',
            'إدريس عن خلف العاشر',
        ])->nullable()->comment('Preferred Quranic recitation style');
        $table->string('profile_image')->nullable();
        $table->boolean('is_active')->default(true);
        $table->rememberToken();
        $table->timestamps();
        
        $table->index(['email', 'is_active']);
        $table->index('national_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

