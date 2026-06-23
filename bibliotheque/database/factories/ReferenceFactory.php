<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReferenceFactory extends Factory
{
    protected $model = \App\Models\Reference::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'subtitle' => fake()->optional()->sentence(6),
            'abstract' => fake()->paragraph(),
            'isbn' => fake()->optional()->isbn13(),
            'publication_year' => fake()->year(),
            'language' => fake()->randomElement(['fr', 'en', 'autre']),
            'document_type_id' => \App\Models\DocumentType::inRandomOrder()->first()?->id ?? 8,
            'category_id' => \App\Models\Category::factory(),
            'publisher_id' => \App\Models\Publisher::factory(),
            'uploaded_by' => \App\Models\User::factory(),
            'cover_image' => function () {
                if (rand(1, 100) <= 70) {
                    $dir = storage_path('app/public/covers');
                    $file = \Database\Seeders\CoverImageGenerator::generate($dir);
                    return $file ? 'covers/' . $file : null;
                }
                return null;
            },
            'file_path' => fake()->optional()->filePath(),
            'pages' => fake()->numberBetween(50, 500),
            'download_count' => fake()->numberBetween(0, 1000),
            'view_count' => fake()->numberBetween(0, 5000),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
        ];
    }
}