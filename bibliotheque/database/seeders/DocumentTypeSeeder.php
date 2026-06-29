<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'article', 'label' => 'Article'],
            ['name' => 'livre', 'label' => 'Livre'],
            ['name' => 'memoire', 'label' => 'Mémoire'],
            ['name' => 'these', 'label' => 'Thèse'],
            ['name' => 'revue', 'label' => 'Revue'],
            ['name' => 'rapport', 'label' => 'Rapport'],
            ['name' => 'guide', 'label' => 'Guide'],
            ['name' => 'autre', 'label' => 'Autre'],
        ];

        foreach ($types as $type) {
            DocumentType::create($type);
        }
    }
}
