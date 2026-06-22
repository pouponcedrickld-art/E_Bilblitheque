<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    protected $model = \App\Models\Author::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'biography' => fake()->paragraph(),
            'nationality' => fake()->country(),
            'birth_date' => fake()->optional()->date(), // Date de naissance (optionnelle)
            'death_date' => fake()->optional()->date(), // Date de décès (optionnelle)
        ];
    }
}