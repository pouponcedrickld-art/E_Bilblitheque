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
            'document_type_id' => \App\Models\DocumentType::inRandomOrder()->first()?->id ?? 8,
            'proposed_file' => fake()->optional()->filePath(),
            'cover_image' => function () {
                if (rand(1, 100) <= 30) {
                    $dir = storage_path('app/public/covers');
                    $file = \Database\Seeders\CoverImageGenerator::generate($dir);
                    return $file ? 'covers/' . $file : null;
                }
                return null;
            },
            'status' => fake()->randomElement(['pending', 'approved_by_manager', 'rejected_by_manager', 'second_review', 'approved', 'rejected', 'published']),
        ];
    }
}