<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = [
            ['name' => 'Les Éditions du Bénin', 'country' => 'Bénin', 'description' => 'Maison d\'édition généraliste basée à Cotonou.'],
            ['name' => 'Nouvelles Éditions Africaines', 'country' => 'Sénégal', 'description' => 'Éditeur panafricain de référence en Afrique francophone.'],
            ['name' => 'Présence Africaine', 'country' => 'France', 'description' => 'Éditeur historique de la littérature africaine et caribéenne.'],
            ['name' => 'Éditions Karthala', 'country' => 'France', 'description' => 'Éditeur spécialisé en sciences humaines sur l\'Afrique.'],
            ['name' => 'L\'Harmattan', 'country' => 'France', 'description' => 'Éditeur académique et littéraire présent dans le monde entier.'],
            ['name' => 'Presses Universitaires de France', 'country' => 'France', 'description' => 'Éditeur universitaire de référence.'],
            ['name' => 'Éditions Dalloz', 'country' => 'France', 'description' => 'Éditeur juridique de référence.'],
        ];

        foreach ($publishers as $publisher) {
            Publisher::factory()->create($publisher);
        }

        $this->command->info('Publishers seeded successfully.');
    }
}
