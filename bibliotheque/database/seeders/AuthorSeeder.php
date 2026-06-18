<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $authors = [
            ['first_name' => 'Victor', 'last_name' => 'Hugo', 'nationality' => 'Française'],
            ['first_name' => 'Albert', 'last_name' => 'Camus', 'nationality' => 'Française'],
            ['first_name' => 'Émile', 'last_name' => 'Zola', 'nationality' => 'Française'],
            ['first_name' => 'Gustave', 'last_name' => 'Flaubert', 'nationality' => 'Française'],
            ['first_name' => 'Molière', 'last_name' => '', 'nationality' => 'Française'],
        ];

        foreach ($authors as $author) {
            Author::factory()->create($author);
        }

        Author::factory(15)->create();
    }
}