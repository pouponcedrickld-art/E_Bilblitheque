<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepositRequestFactory extends Factory
{
    protected $model = \App\Models\DepositRequest::class;

    public function definition(): array
    {
        return [
            'applicant_id' => \App\Models\User::factory(),
            'assigned_manager_id' => \App\Models\User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'proposed_file' => fake()->optional()->filePath(),
            'status' => fake()->randomElement(['pending', 'approved_by_manager', 'rejected_by_manager', 'second_review', 'approved', 'rejected', 'published']), // Statut de la demande
        ];
    }
}