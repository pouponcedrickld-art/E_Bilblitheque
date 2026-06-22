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
            'language' => fake()->randomElement(['fr', 'en', 'autre']), // Langue du document
            'document_type' => fake()->randomElement(['livre', 'memoire', 'these', 'article', 'revue', 'rapport', 'guide', 'autre']), // Type de document
            'category_id' => \App\Models\Category::factory(),
            'publisher_id' => \App\Models\Publisher::factory(),
            'uploaded_by' => \App\Models\User::factory(),
            'cover_image' => fake()->optional()->imageUrl(),
            'file_path' => fake()->optional()->filePath(),
            'pages' => fake()->numberBetween(50, 500),
            'download_count' => fake()->numberBetween(0, 1000), // Compteur de téléchargements
            'view_count' => fake()->numberBetween(0, 5000), // Compteur de consultations
            'status' => fake()->randomElement(['draft', 'published', 'archived']), // Statut de publication
        ];
    }
}