<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'name' => 'دورة ' . fake()->word(),
            'description' => fake()->sentence(),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+2 month', '+3 month')->format('Y-m-d'),
            'type' => fake()->randomElement(['open', 'closed', 'online']),
            'mosque_id' => null, // يمكنك تعيين مسجد عشوائي هنا إذا لزم الأمر
            'image_path' => null, // يمكنك تعيين مسار صورة عشوائية إذا
            'max_students' => fake()->numberBetween(20, 100),
            'current_students' => random_int(0, 20), // يمكنك تعيين عدد الطلاب الحاليين عشو
            'is_active' => true,
            'is_registration_open' => true,
            'created_by' => 1, // تأكد من وجود Admin user
        ];
    }
}
