<?php

namespace Database\Factories;

use App\Models\ReviewLog;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewLogFactory extends Factory
{
    protected $model = ReviewLog::class;

    public function definition(): array
    {
        return [
            'student_id' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))->inRandomOrder()->first()->id ?? User::factory(),
            'sheikh_id' => User::whereHas('roles', fn($q) => $q->where('name', 'sheikh'))->inRandomOrder()->first()->id ?? User::factory(),
            'course_id' => Course::inRandomOrder()->first()->id ?? Course::factory(),
            'group_id' => User::whereHas('roles', fn($q) => $q->where('name', 'group'))->inRandomOrder()->first()?->id ?? null,
            'start_surah' => fake()->text(5, 114),
            'end_surah' => fake()->text(5, 114),
            'start_ayah' => fake()->numberBetween(1, 10),
            'end_ayah' => fake()->numberBetween(10, 20),

            'evaluation' => fake()->randomElement(['excellent', 'very_good', 'good', 'needs_improvement', 'poor']),
            'notes' => fake()->paragraph(),
            'session_date' => fake()->dateTime()->format('Y-m-d'),
            // 'session_time' => fake()->time()->format('H:i:s'),
        ];
    }
}
