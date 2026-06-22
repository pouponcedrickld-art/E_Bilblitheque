<?php

namespace Database\Seeders;

use App\Models\Reference;
use App\Models\Author;
use App\Models\Keyword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            $keywordNames = ['php', 'laravel', 'programmation', 'web', 'database', 'api', 'frontend', 'backend', 'devops', 'cloud'];
            $selectedNames = array_rand(array_flip($keywordNames), rand(2, 5));

            $keywordIds = [];
            foreach ((array) $selectedNames as $name) {
                $keyword = Keyword::firstOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name)]
                );
                $keywordIds[] = $keyword->id;
            }

            $reference->keywords()->attach($keywordIds);
        });
    }
}
