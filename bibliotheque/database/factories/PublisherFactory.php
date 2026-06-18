<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PublisherFactory extends Factory
{
    protected $model = \App\Models\Publisher::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'country' => fake()->country(),
            'website' => fake()->optional()->url(),
        ];
    }
}