<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReferenceRequest;
use App\Http\Resources\ReferenceResource;
use App\Models\Notification;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReferenceController extends Controller
{
    // Liste paginée des références avec filtres (catégorie, type, langue, statut, recherche)
    public function index(Request $request)
    {
        $query = Reference::with(['category', 'publisher', 'documentType', 'authors', 'keywords', 'uploader']);

        // Les visiteurs non connectés ne voient que les références publiées
        if (!$request->user()) {
            $query->where('status', 'published');
        }

        // Filtres optionnels
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('abstract', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filtre par mot-clé
        if ($request->has('keyword')) {
            $query->whereHas('keywords', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->keyword}%");
            });
        }

        // Filtre par auteur
        if ($request->has('author')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->author}%")
                    ->orWhere('last_name', 'like', "%{$request->author}%");
            });
        }

        return ReferenceResource::collection($query->paginate(15));
    }

    // Retourne les 6 références mises en avant et publiées
    public function featured()
    {
        $references = Reference::with(['category', 'authors', 'keywords'])
            ->where('is_featured', true)
            ->where('status', 'published')
            ->latest()
            ->take(6)
            ->get();

        return ReferenceResource::collection($references);
    }

    // Crée une référence avec upload possible de couverture et fichier
    public function store(StoreReferenceRequest $request)
    {
        $data = $request->except(['cover_image', 'file_path', 'author_ids', 'keyword_ids']);

        // Upload de l'image de couverture
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        // Upload du fichier PDF/DOC
        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        $data['uploaded_by'] = $request->user()->id;
        $data['status'] = 'draft';

        $reference = Reference::create($data);

        // Attache les auteurs et mots-clés en relation many-to-many
        if ($request->has('author_ids')) {
            $reference->authors()->attach($request->author_ids);
        }

        if ($request->has('keyword_ids')) {
            $reference->keywords()->attach($request->keyword_ids);
        }

        return new ReferenceResource($reference->load(['category', 'publisher', 'documentType', 'authors', 'keywords']));
    }

    // Détail d'une référence avec incrémentation du compteur de vues
    public function show(Request $request, Reference $reference)
    {
        $reference->increment('view_count');

        $load = ['category', 'publisher', 'documentType', 'authors', 'keywords', 'uploader'];

        // Les stats détaillées (téléchargements, vues) sont réservées aux utilisateurs connectés
        if ($request->user()) {
            $load = array_merge($load, ['downloads', 'views']);
        }

        return new ReferenceResource($reference->load($load));
    }

    // Met à jour une référence (remplace l'image si fournie, synchronise les mots-clés)
    public function update(Request $request, Reference $reference)
    {
        $request->validate([
            'title' => 'string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|unique:references,isbn,' . $reference->id,
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'in:fr,en,autre',
            'document_type_id' => 'nullable|exists:document_types,id',
            'category_id' => 'exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'status' => 'in:draft,published,archived',
            'pages' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|image|max:5120',
            'keyword_ids' => 'nullable|array',
            'keyword_ids.*' => 'exists:keywords,id',
            'is_featured' => 'boolean',
            'allow_download' => 'boolean',
        ]);

        $data = $request->except(['keyword_ids', 'cover_image']);

        // Remplace l'ancienne couverture si une nouvelle est uploadée
        if ($request->hasFile('cover_image')) {
            if ($reference->cover_image) {
                Storage::disk('public')->delete($reference->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $reference->update($data);

        // Synchronise les mots-clés (remplace les anciens)
        if ($request->has('keyword_ids')) {
            $reference->keywords()->sync($request->keyword_ids);
        }

        return new ReferenceResource($reference->load(['category', 'publisher', 'documentType', 'authors', 'keywords']));
    }

    // Supprime une référence et ses fichiers associés
    public function destroy(Reference $reference)
    {
        if ($reference->cover_image) {
            Storage::disk('public')->delete($reference->cover_image);
        }
        if ($reference->file_path) {
            Storage::disk('public')->delete($reference->file_path);
        }

        $reference->delete();

        return response()->json(null, 204);
    }

    // Affiche le fichier PDF/DOC dans le navigateur (lecture seule)
    public function read(Request $request, Reference $reference)
    {
        if (!$reference->file_path || !Storage::disk('public')->exists($reference->file_path)) {
            return response()->json(['message' => 'Document non disponible.'], 404);
        }

        // Les visiteurs non connectés ne peuvent lire que les références publiées
        if (!$request->user() && $reference->status !== 'published') {
            return response()->json(['message' => 'Document non disponible.'], 404);
        }

        // Enregistre la consultation
        $reference->views()->create([
            'user_id' => $request->user()?->id,
            'viewed_at' => now(),
        ]);

        $reference->increment('view_count');

        $path = Storage::disk('public')->path($reference->file_path);
        $mime = Storage::disk('public')->mimeType($reference->file_path);

        return response()->file($path, ['Content-Type' => $mime]);
    }

    // Télécharge le fichier PDF/DOC avec compteur
    public function download(Request $request, Reference $reference)
    {
        $this->authorize('download', $reference);

        if (!$reference->file_path || !Storage::disk('public')->exists($reference->file_path)) {
            return response()->json(['message' => 'Fichier non disponible.'], 404);
        }

        // Enregistre le téléchargement
        $reference->downloads()->create([
            'user_id' => $request->user()?->id,
            'downloaded_at' => now(),
        ]);

        $reference->increment('download_count');

        return Storage::disk('public')->download($reference->file_path);
    }

    // Admin : force le téléchargement sur une référence (outrepasse le refus du propriétaire)
    public function forceDownload(Request $request, Reference $reference)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($reference->allow_download === true) {
            return response()->json(['message' => 'Le téléchargement est déjà autorisé.'], 422);
        }

        $reference->update(['allow_download' => true]);

        Notification::create([
            'user_id' => $reference->uploaded_by,
            'title' => 'Téléchargement autorisé',
            'message' => "L'administrateur a autorisé le téléchargement de votre publication \"{$reference->title}\" malgré votre refus.",
            'type' => 'information',
        ]);

        return response()->json([
            'message' => 'Téléchargement autorisé. Le propriétaire a été notifié.',
            'allow_download' => true,
        ]);
    }
}