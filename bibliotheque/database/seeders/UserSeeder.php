<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Responsable RH
        User::factory()->create([
            'first_name' => 'RH',
            'last_name' => 'Manager',
            'email' => 'rh@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'responsable_rh',
            'status' => 'active',
        ]);

        // Responsable Demande
        User::factory()->create([
            'first_name' => 'Demande',
            'last_name' => 'Manager',
            'email' => 'demande@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'responsable_demande',
            'status' => 'active',
        ]);

        // Utilisateur standard
        User::factory()->create([
            'first_name' => 'User',
            'last_name' => 'Standard',
            'email' => 'user@bibliotheque.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // 10 utilisateurs aléatoires
        User::factory(10)->create();
    }
}



//
// j'ai toujours le probleme de navbar
// gerer les utilsateur ne c onduit toujours null part 
// nouvelle reference ne me  permet toujours pas de creer mes nouvelle reference
// 
// 
// 
// 


