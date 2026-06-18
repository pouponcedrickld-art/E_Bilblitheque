<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = [
            ['name' => 'Gallimard', 'country' => 'France'],
            ['name' => 'Le Seuil', 'country' => 'France'],
            ['name' => 'Albin Michel', 'country' => 'France'],
            ['name' => 'Flammarion', 'country' => 'France'],
            ['name' => 'Larousse', 'country' => 'France'],
            ['name' => 'Springer', 'country' => 'Allemagne'],
            ['name' => 'Oxford University Press', 'country' => 'Royaume-Uni'],
            ['name' => 'Harvard University Press', 'country' => 'États-Unis'],
        ];

        foreach ($publishers as $publisher) {
            Publisher::factory()->create($publisher);
        }

        Publisher::factory(5)->create();
    }
}