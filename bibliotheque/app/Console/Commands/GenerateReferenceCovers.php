<?php

namespace App\Console\Commands;

use App\Models\Reference;
use App\Services\CoverService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateReferenceCovers extends Command
{
    protected $signature = 'references:generate-covers
        {--force : Regénérer les couvertures même si déjà existantes}
        {--no-pexels : Ignorer Pexels même si une clé API est configurée}';

    protected $description = 'Récupère de vraies couvertures pour les références via Open Library et Pexels';

    public function handle(CoverService $coverService): int
    {
        $force = $this->option('force');

        $query = Reference::with(['category', 'authors']);

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('cover_image')
                  ->orWhere('cover_image', '');
            });
        }

        $references = $query->get();
        $total = $references->count();

        if ($total === 0) {
            $this->info('Aucune référence à traiter.');
            return Command::SUCCESS;
        }

        $this->info("Traitement de {$total} référence(s)...");
        $this->newLine();

        $stats = ['openlibrary' => 0, 'pexels' => 0, 'skipped' => 0, 'notfound' => 0];

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat("  %current%/%max% [%bar%] %percent:3s%% %message%\n");
        $bar->setMessage('Démarrage...');
        $bar->start();

        foreach ($references as $reference) {
            $bar->setMessage($reference->title);
            $oldImage = $reference->cover_image;

            try {
                $coverPath = $coverService->fetchCover($reference);

                if ($coverPath) {
                    if ($coverPath !== $oldImage) {
                        $reference->update(['cover_image' => $coverPath]);
                        if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                            Storage::disk('public')->delete($oldImage);
                        }
                    }

                    $sourcePrefix = explode('_', basename($coverPath))[0];
                    if ($sourcePrefix === 'ol') {
                        $stats['openlibrary']++;
                    } else {
                        $stats['pexels']++;
                    }
                } else {
                    if ($reference->cover_image) {
                        $reference->update(['cover_image' => null]);
                    }
                    $stats['notfound']++;
                }
            } catch (\Exception $e) {
                $stats['notfound']++;
            }

            $bar->advance();
            usleep(100000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Source', 'Nombre'],
            [
                ['Open Library (vraies couvertures)', $stats['openlibrary']],
                ['Pexels (photos thématiques)', $stats['pexels']],
                ['Aucune trouvée', $stats['notfound']],
            ]
        );

        $this->newLine();
        $this->info('✅ Terminé !');

        if ($stats['notfound'] > 0) {
            $this->warn("⚠️  {$stats['notfound']} référence(s) sans couverture. Pour les livres thématiques sans ISBN réel, configure PEXELS_API_KEY dans .env");
            $this->info('   → Obtiens une clé gratuite sur https://www.pexels.com/api/');
        }

        return Command::SUCCESS;
    }
}
