<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Informatique', 'slug' => 'informatique', 'description' => 'Livres sur l\'informatique et la programmation'],
            ['name' => 'Droit', 'slug' => 'droit', 'description' => 'Livres de droit et législation'],
            ['name' => 'Médecine', 'slug' => 'medecine', 'description' => 'Livres de médecine et santé'],
            ['name' => 'Économie', 'slug' => 'economie', 'description' => 'Livres d\'économie et finance'],
            ['name' => 'Histoire', 'slug' => 'histoire', 'description' => 'Livres d\'histoire et géographie'],
            ['name' => 'Littérature', 'slug' => 'litterature', 'description' => 'Romans et œuvres littéraires'],
            ['name' => 'Science', 'slug' => 'science', 'description' => 'Livres scientifiques'],
            ['name' => 'Technologie', 'slug' => 'technologie', 'description' => 'Livres sur les nouvelles technologies'],
        ];

        foreach ($categories as $category) {
            Category::factory()->create($category);
        }

        Category::factory(5)->create();
    }
}