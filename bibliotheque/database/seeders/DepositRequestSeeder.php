<?php

namespace Database\Seeders;

use App\Models\DepositRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepositRequestSeeder extends Seeder
{
    public function run(): void
    {
        $applicants = User::where('role', 'user')->take(5)->get();
        $managers = User::where('role', 'responsable_demande')->get();

        if ($managers->isEmpty()) {
            $managers = User::where('role', 'admin')->get();
        }

        $managerIds = $managers->pluck('id')->toArray();

        $requests = [
            [
                'title' => 'Demande de dépôt : Mémoire sur la microfinance',
                'description' => 'Je souhaite déposer mon mémoire de master sur la microfinance en milieu rural béninois.',
                'status' => 'pending',
            ],
            [
                'title' => 'Publication : Article sur le changement climatique',
                'description' => 'Proposition d\'article scientifique sur l\'impact du changement climatique dans le département du Zou.',
                'status' => 'approved_by_manager',
            ],
            [
                'title' => 'Dépôt de guide pédagogique',
                'description' => 'Guide pratique pour l\'enseignement des mathématiques dans les écoles secondaires du Bénin.',
                'status' => 'published',
            ],
            [
                'title' => 'Thèse de doctorat : Droit constitutionnel',
                'description' => 'Thèse portant sur l\'évolution du droit constitutionnel béninois depuis la conférence nationale de 1990.',
                'status' => 'second_review',
            ],
            [
                'title' => 'Recueil de poèmes contemporains',
                'description' => 'Recueil de poésie traitant des thèmes de l\'exil, de l\'identité et de la renaissance africaine.',
                'status' => 'rejected',
            ],
            [
                'title' => 'Étude sur l\'entrepreneuriat féminin',
                'description' => 'Rapport d\'étude sur les défis et opportunités de l\'entrepreneuriat féminin au Bénin.',
                'status' => 'approved',
            ],
            [
                'title' => 'Manuel de médecine tropicale',
                'description' => 'Manuel à destination des étudiants en médecine sur les pathologies tropicales courantes.',
                'status' => 'pending',
            ],
        ];

        $i = 0;
        foreach ($requests as $data) {
            $applicant = $applicants[$i % $applicants->count()];

            DepositRequest::factory()->create(array_merge($data, [
                'applicant_id' => $applicant->id,
                'assigned_manager_id' => $managerIds[array_rand($managerIds)],
            ]));

            $i++;
        }

        $this->command->info('Deposit requests seeded successfully.');
    }
}
