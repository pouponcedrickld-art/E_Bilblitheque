<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crée les utilisateurs par défaut (admin, RH, responsable demande) et des utilisateurs factices
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Rh',
            'last_name' => 'Manager',
            'email' => 'rh@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'responsable_rh',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Demande',
            'last_name' => 'Manager',
            'email' => 'demande@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'responsable_demande',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Aminata',
            'last_name' => 'Diallo',
            'email' => 'demande2@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'responsable_demande',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Kouassi',
            'email' => 'jean.kouassi@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Fatou',
            'last_name' => 'Diop',
            'email' => 'fatou.diop@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Kofi',
            'last_name' => 'Adebayor',
            'email' => 'kofi.adebayor@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Amandine',
            'last_name' => 'Hounkpatin',
            'email' => 'amandine.hounkpatin@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Mamadou',
            'last_name' => 'Traoré',
            'email' => 'mamadou.traore@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        User::factory(10)->create();

        $this->command->info('Users seeded successfully.');
    }
}
