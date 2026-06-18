<?php

namespace Database\Seeders;

use App\Models\DepositRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepositRequestSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->take(5)->get();
        $managers = User::where('role', 'responsable_demande')->get();

        foreach ($users as $user) {
            DepositRequest::factory()->create([
                'applicant_id' => $user->id,
                'assigned_manager_id' => $managers->random()->id,
            ]);
        }

        DepositRequest::factory(10)->create();
    }
}