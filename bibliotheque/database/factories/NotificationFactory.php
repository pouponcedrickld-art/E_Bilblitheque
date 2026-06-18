<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = \App\Models\Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['system', 'validation', 'publication', 'information']),
            'is_read' => fake()->boolean(),
        ];
    }
}