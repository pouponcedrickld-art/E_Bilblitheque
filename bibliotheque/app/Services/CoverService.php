<?php

namespace App\Services;

use App\Models\Reference;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoverService
{
    protected int $width = 400;
    protected int $height = 600;

    protected array $unsplashCovers = [
        'Droit' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73',
        'Économie' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f',
        'Histoire' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e',
        'Sciences' => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb',
        'Littérature' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570',
        'Médecine' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef',
        'Philosophie' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794',
    ];

    /**
     * Point d'entrée principal : récupère une vraie image de couverture
     * pour une référence.
     *
     * Ordre :
     * 1. Open Library par ISBN (gratuit, 0 clé)
     * 2. Open Library par titre + auteur (gratuit, 0 clé)
     * 3. Unsplash par catégorie (images du prototype)
     * 4. Pexels par catégorie (si PEXELS_API_KEY configurée dans .env)
     * 5. Retourne null → le frontend affiche un placeholder
     */
    public function fetchCover(Reference $reference): ?string
    {
        // 1 — Open Library par ISBN
        if ($reference->isbn) {
            $cleanIsbn = preg_replace('/[^0-9X]/', '', $reference->isbn);
            if (strlen($cleanIsbn) >= 10) {
                $result = $this->openLibraryByIsbn($cleanIsbn);
                if ($result) return $result;
            }
        }

        // 2 — Open Library par titre + auteur
        $result = $this->openLibraryByTitle($reference);
        if ($result) return $result;

        // 3 — Unsplash (images du prototype, par catégorie)
        $categoryName = $reference->category?->name;
        if ($categoryName && isset($this->unsplashCovers[$categoryName])) {
            $result = $this->downloadUnsplash($this->unsplashCovers[$categoryName]);
            if ($result) return $result;
        }

        // 4 — Pexels (si clé configurée)
        $pexelsKey = config('services.pexels.key');
        if ($pexelsKey) {
            $result = $this->pexelsByCategory($reference, $pexelsKey);
            if ($result) return $result;
        }

        return null;
    }

    // ─── Open Library ─────────────────────────────────────────────────

    protected function openLibraryByIsbn(string $isbn): ?string
    {
        try {
            $url = 'https://covers.openlibrary.org/b/isbn/' . $isbn . '-L.jpg';

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $imageContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode < 200 || $httpCode >= 400 || !$imageContent || strlen($imageContent) < 1000) {
                return null;
            }

            if ($contentType && !str_starts_with($contentType, 'image/')) {
                return null;
            }

            return $this->saveImage($imageContent, 'ol');
        } catch (\Exception) {
            return null;
        }
    }

    protected function openLibraryByTitle(Reference $reference): ?string
    {
        try {
            $query = urlencode($reference->title);
            if ($reference->authors->isNotEmpty()) {
                $authorNames = $reference->authors->pluck('full_name')->implode(' ');
                $query .= '+' . urlencode($authorNames);
            }

            $searchUrl = 'https://openlibrary.org/search.json?q=' . $query . '&limit=5';
            $response = Http::timeout(10)->get($searchUrl);

            if (!$response->successful()) return null;

            $data = $response->json();
            $docs = $data['docs'] ?? [];

            foreach ($docs as $doc) {
                if (!empty($doc['cover_i'])) {
                    $coverUrl = 'https://covers.openlibrary.org/b/id/' . $doc['cover_i'] . '-L.jpg';

                    $imageContent = @file_get_contents($coverUrl);
                    if ($imageContent && strlen($imageContent) >= 1000) {
                        return $this->saveImage($imageContent, 'ol');
                    }
                }
            }
        } catch (\Exception) {
            return null;
        }

        return null;
    }

    // ─── Unsplash ─────────────────────────────────────────────────────

    protected function downloadUnsplash(string $photoId): ?string
    {
        $url = $photoId . '?w=800&h=1200&fit=crop&auto=format';

        try {
            $imageContent = @file_get_contents($url);
            if (!$imageContent || strlen($imageContent) < 1000) return null;

            return $this->saveImage($imageContent, 'us');
        } catch (\Exception) {
            return null;
        }
    }

    // ─── Pexels ───────────────────────────────────────────────────────

    protected function pexelsByCategory(Reference $reference, string $apiKey): ?string
    {
        $keywords = $this->getSearchKeywords($reference);
        if (empty($keywords)) return null;

        $keyword = urlencode($keywords[0]);

        try {
            $ch = curl_init('https://api.pexels.com/v1/search?query=' . $keyword . '&orientation=portrait&per_page=5');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: ' . $apiKey],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) return null;

            $data = json_decode($response, true);
            $photos = $data['photos'] ?? [];

            if (empty($photos)) return null;

            $photo = $photos[array_rand($photos)];
            $photoUrl = $photo['src']['large'] ?? $photo['src']['original'] ?? null;

            if (!$photoUrl) return null;

            $imageContent = @file_get_contents($photoUrl);
            if (!$imageContent || strlen($imageContent) < 1000) return null;

            return $this->saveImage($imageContent, 'pex');
        } catch (\Exception) {
            return null;
        }
    }

    // ─── Utilitaires ──────────────────────────────────────────────────

    protected function saveImage(string $imageContent, string $prefix): string
    {
        $dir = Storage::disk('public')->path('covers');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $prefix . '_' . Str::random(20) . '.jpg';
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        $img = @imagecreatefromstring($imageContent);
        if ($img === false) {
            file_put_contents($path, $imageContent);
            return 'covers/' . $filename;
        }

        $origW = imagesx($img);
        $origH = imagesy($img);

        $newH = $this->height;
        $newW = intval($origW * ($newH / $origH));

        if ($newW < $this->width * 0.5) {
            $newW = $this->width;
            $newH = intval($origH * ($newW / $origW));
        }

        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($img);

        imagejpeg($resized, $path, 90);
        imagedestroy($resized);

        return 'covers/' . $filename;
    }

    protected function getSearchKeywords(Reference $reference): array
    {
        $categoryName = $reference->category?->name;

        $categoryKeywords = [
            'Droit' => ['law', 'justice', 'legal', 'constitution'],
            'Sciences' => ['science', 'laboratory', 'research', 'scientific'],
            'Histoire' => ['history', 'ancient', 'historical', 'heritage'],
            'Médecine' => ['medicine', 'health', 'medical', 'hospital'],
            'Littérature' => ['literature', 'books', 'library', 'reading'],
            'Économie' => ['economy', 'finance', 'business', 'economics'],
            'Philosophie' => ['philosophy', 'wisdom', 'thought', 'ancient'],
            'Informatique' => ['technology', 'computer', 'coding', 'digital'],
            'Sociologie' => ['society', 'people', 'community', 'social'],
            'Géographie' => ['geography', 'map', 'globe', 'landscape'],
            'Sciences Politiques' => ['politics', 'government', 'diplomacy', 'parliament'],
            'Arts' => ['art', 'gallery', 'creative', 'painting'],
            'Technologie' => ['technology', 'innovation', 'digital', 'future'],
        ];

        $keywords = $categoryKeywords[$categoryName] ?? ['book', 'library', 'knowledge'];
        $titleWords = explode(' ', $reference->title);

        $combined = array_merge(
            $keywords,
            array_slice($titleWords, 0, 3)
        );

        return array_unique(array_filter($combined, fn($w) => strlen($w) > 2));
    }
}
