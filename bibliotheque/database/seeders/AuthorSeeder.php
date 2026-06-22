<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $authors = [
            ['first_name' => 'Félix', 'last_name' => 'Houphouët-Boigny', 'nationality' => 'Ivoirienne', 'biography' => 'Homme politique et écrivain ivoirien, premier président de la Côte d\'Ivoire.'],
            ['first_name' => 'Cheikh', 'last_name' => 'Anta Diop', 'nationality' => 'Sénégalaise', 'biography' => 'Historien, anthropologue et homme politique sénégalais, spécialiste de l\'Égypte antique.'],
            ['first_name' => 'Mariama', 'last_name' => 'Bâ', 'nationality' => 'Sénégalaise', 'biography' => 'Romancière sénégalaise, auteure de "Une si longue lettre".'],
            ['first_name' => 'Ahmadou', 'last_name' => 'Kourouma', 'nationality' => 'Ivoirienne', 'biography' => 'Écrivain ivoirien, auteur de "Les Soleils des indépendances".'],
            ['first_name' => 'Amadou', 'last_name' => 'Hampâté Bâ', 'nationality' => 'Malienne', 'biography' => 'Écrivain et ethnologue malien, défenseur de la tradition orale africaine.'],
            ['first_name' => 'Léonard', 'last_name' => 'Yao', 'nationality' => 'Béninoise', 'biography' => 'Juriste béninois, spécialiste en droit constitutionnel africain.'],
            ['first_name' => 'Geneviève', 'last_name' => 'Ahouansou', 'nationality' => 'Béninoise', 'biography' => 'Médecin et chercheuse béninoise en santé publique.'],
            ['first_name' => 'Alassane', 'last_name' => 'Ouattara', 'nationality' => 'Ivoirienne', 'biography' => 'Économiste et homme politique ivoirien.'],
            ['first_name' => 'Joseph', 'last_name' => 'Ki-Zerbo', 'nationality' => 'Burkinabè', 'biography' => 'Historien burkinabè, spécialiste de l\'histoire africaine.'],
            ['first_name' => 'Nelson', 'last_name' => 'Mandela', 'nationality' => 'Sud-Africaine', 'biography' => 'Homme d\'État sud-africain, prix Nobel de la paix.'],
            ['first_name' => 'Chinua', 'last_name' => 'Achebe', 'nationality' => 'Nigériane', 'biography' => 'Romancier nigérian, auteur de "Le Monde s\'effondre".'],
            ['first_name' => 'Wole', 'last_name' => 'Soyinka', 'nationality' => 'Nigériane', 'biography' => 'Écrivain nigérian, prix Nobel de littérature.'],
            ['first_name' => 'Léopold', 'last_name' => 'Sédar Senghor', 'nationality' => 'Sénégalaise', 'biography' => 'Poète et homme d\'État sénégalais, fondateur de la Négritude.'],
            ['first_name' => 'Benoît', 'last_name' => 'Houndeton', 'nationality' => 'Béninoise', 'biography' => 'Philosophe béninois, spécialiste de la philosophie africaine.'],
            ['first_name' => 'Fatou', 'last_name' => 'Keïta', 'nationality' => 'Malienne', 'biography' => 'Romancière malienne, engagée pour les droits des femmes.'],
            ['first_name' => 'Djibril', 'last_name' => 'Tamsir Niane', 'nationality' => 'Guinéenne', 'biography' => 'Historien et écrivain guinéen, spécialiste de l\'empire du Mali.'],
            ['first_name' => 'Koffi', 'last_name' => 'Kokou', 'nationality' => 'Togolaise', 'biography' => 'Sociologue togolais, chercheur en sciences sociales.'],
            ['first_name' => 'Michaëlle', 'last_name' => 'Jean', 'nationality' => 'Haïtienne', 'biography' => 'Diplomate et femme politique haïtienne, ancienne gouverneure générale du Canada.'],
            ['first_name' => 'Tanella', 'last_name' => 'Boni', 'nationality' => 'Ivoirienne', 'biography' => 'Écrivaine et poétesse ivoirienne, philosophe de formation.'],
            ['first_name' => 'Alain', 'last_name' => 'Mabanckou', 'nationality' => 'Congolaise', 'biography' => 'Romancier congolais, auteur de "Mémoires de porc-épic".'],
            ['first_name' => 'Yambo', 'last_name' => 'Ouologuem', 'nationality' => 'Malienne', 'biography' => 'Écrivain malien, auteur du "Devoir de violence".'],
            ['first_name' => 'Sylvain', 'last_name' => 'Bemba', 'nationality' => 'Congolaise', 'biography' => 'Journaliste et écrivain congolais.'],
        ];

        foreach ($authors as $author) {
            Author::factory()->create($author);
        }

        $this->command->info('Authors seeded successfully.');
    }
}
