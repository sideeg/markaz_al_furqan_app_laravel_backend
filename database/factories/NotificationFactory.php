<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['info', 'warning', 'error', 'success']),
            
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
