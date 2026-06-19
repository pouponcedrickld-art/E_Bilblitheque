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




////concernant le rh 
// lorque je clic sur nouveau Journal d'activité je suis rediriger ver une  page blanche 
// lorque je clic sur nouveau utilisateur je suis rediriger ver une  page blanche 
// lorque je clic sur nouveau gere les utilisateurs je suis rediriger ver une  page blanche 
// 
// 
// 
// concerant l'admin 
// pourla nav bar elle semble bouger elle n'est pas la même dans toutes les vue (il y a aussi ce bug chez le users standars )
// 
// lorsque je clic sur nouvlle reference je ne recoit pas le formulaire mais jes usi redireger vers la page d'afffiche de formulaire se qui n'est pasl'effet voulu 
// lorsque l'admin essais de modifier un user il y a un 403 se qui n'est pas conforme a ce que je voudrais pour  mon site 
//  
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 