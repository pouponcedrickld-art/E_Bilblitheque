<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Droit', 'slug' => 'droit', 'description' => 'Ouvrages juridiques et législation', 'status' => 'active'],
            ['name' => 'Sciences', 'slug' => 'sciences', 'description' => 'Sciences exactes et naturelles', 'status' => 'active'],
            ['name' => 'Histoire', 'slug' => 'histoire', 'description' => 'Histoire générale et africaine', 'status' => 'active'],
            ['name' => 'Médecine', 'slug' => 'medecine', 'description' => 'Médecine et santé publique', 'status' => 'active'],
            ['name' => 'Littérature', 'slug' => 'litterature', 'description' => 'Romans, poésies et œuvres littéraires', 'status' => 'active'],
            ['name' => 'Économie', 'slug' => 'economie', 'description' => 'Économie et gestion', 'status' => 'active'],
            ['name' => 'Philosophie', 'slug' => 'philosophie', 'description' => 'Philosophie et réflexion', 'status' => 'active'],
            ['name' => 'Informatique', 'slug' => 'informatique', 'description' => 'Informatique et programmation', 'status' => 'active'],
            ['name' => 'Sociologie', 'slug' => 'sociologie', 'description' => 'Sociologie et anthropologie', 'status' => 'active'],
            ['name' => 'Géographie', 'slug' => 'geographie', 'description' => 'Géographie et aménagement du territoire', 'status' => 'active'],
            ['name' => 'Sciences Politiques', 'slug' => 'sciences-politiques', 'description' => 'Sciences politiques et relations internationales', 'status' => 'active'],
            ['name' => 'Arts', 'slug' => 'arts', 'description' => 'Arts, musique et culture', 'status' => 'active'],
            ['name' => 'Technologie', 'slug' => 'technologie', 'description' => 'Technologies et innovations', 'status' => 'active'],
        ];

        foreach ($categories as $category) {
            Category::factory()->create($category);
        }

        $this->command->info('Categories seeded successfully.');
    }
}
