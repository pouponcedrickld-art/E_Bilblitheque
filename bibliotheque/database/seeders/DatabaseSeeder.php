<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Lance tous les seeders dans l'ordre défini
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            AuthorSeeder::class,
            PublisherSeeder::class,
            DocumentTypeSeeder::class,
            KeywordSeeder::class,
            ReferenceSeeder::class,
            DepositRequestSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
