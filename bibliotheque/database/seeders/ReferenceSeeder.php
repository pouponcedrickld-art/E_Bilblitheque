<?php

namespace Database\Seeders;

use App\Models\Reference;
use App\Models\Author;
use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 30 références
        Reference::factory(30)->create()->each(function ($reference) {
            // Attacher 1 à 3 auteurs aléatoires
            $authors = Author::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $reference->authors()->attach($authors);

            // Ajouter 2 à 5 mots-clés
            $keywords = ['php', 'laravel', 'programmation', 'web', 'database', 'api', 'frontend', 'backend', 'devops', 'cloud'];
            $selectedKeywords = array_rand(array_flip($keywords), rand(2, 5));

            foreach ((array) $selectedKeywords as $keyword) {
                $reference->keywords()->create(['keyword' => $keyword]);
            }
        });
    }
}