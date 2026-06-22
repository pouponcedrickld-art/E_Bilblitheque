<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = \App\Models\Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name), // Slug généré à partir du nom
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['active', 'inactive']), // Statut d'activation
        ];
    }
}