<?php

namespace Database\Factories;

use Database\Seeders\CoverImageGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class ReferenceFactory extends Factory
{
    protected $model = \App\Models\Reference::class;

    protected static $documents = null;

    protected static function getDocuments(): array
    {
        if (static::$documents === null) {
            $files = Storage::disk('public')->files('documents');
            static::$documents = array_values(array_filter($files, function ($file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                return in_array($ext, ['pdf', 'docx', 'html', 'svg']);
            }));
        }
        return static::$documents;
    }

    public function definition(): array
    {
        $docs = static::getDocuments();

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
                    $file = CoverImageGenerator::generate($dir);
                    return $file ? 'covers/' . $file : null;
                }
                return null;
            },
            'file_path' => $docs ? $docs[array_rand($docs)] : null,
            'allow_download' => fake()->boolean(70),
            'pages' => fake()->numberBetween(50, 500),
            'download_count' => fake()->numberBetween(0, 1000),
            'view_count' => fake()->numberBetween(0, 5000),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\Reference $reference) {
            if ($reference->file_path && Storage::disk('public')->exists($reference->file_path)) {
                $reference->file_size = Storage::disk('public')->size($reference->file_path);
                $reference->save();
            }
        });
    }
}
