<?php

namespace Database\Seeders;

use App\Models\Keyword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KeywordSeeder extends Seeder
{
    public function run(): void
    {
        // Crée les mots-clés du thésaurus documentaire
        $keywords = [
            'droit constitutionnel',
            'droit des affaires',
            'santé publique',
            'médecine tropicale',
            'histoire africaine',
            'développement économique',
            'philosophie africaine',
            'littérature francophone',
            'sociologie urbaine',
            'changement climatique',
            'intelligence artificielle',
            'programmation web',
            'droits de l\'homme',
            'genre et développement',
            'éducation',
            'agriculture durable',
            'entrepreneuriat',
        ];

        foreach ($keywords as $name) {
            Keyword::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }

        $this->command->info('Keywords seeded successfully.');
    }
}
