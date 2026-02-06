<?php

namespace Database\Factories;

use App\Models\HifzLog;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class HifzLogFactory extends Factory
{
    protected $model = HifzLog::class;

    public function definition(): array
    {
        return [
            'student_id' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))->inRandomOrder()->first()->id ?? User::factory(),
            'sheikh_id' => User::whereHas('roles', fn($q) => $q->where('name', 'sheikh'))->inRandomOrder()->first()->id ?? User::factory(),
            'course_id' => Course::inRandomOrder()->first()->id ?? Course::factory(),
            'start_surah' => fake()->text(5, 114),
            'start_ayah' => fake()->numberBetween(1, 10),
            'end_surah' => fake()->text(5, 114),
            'end_ayah' => fake()->numberBetween(10, 20),
            'evaluation' => fake()->randomElement(['excellent', 'very_good', 'good', 'needs_improvement', 'poor']),
            'notes' => fake()->sentence(),
            'session_date' => fake()->dateTime()->format('Y-m-d'),
            'session_time' => fake()->time(),
        ];
    }
}
