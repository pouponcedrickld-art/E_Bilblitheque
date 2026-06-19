<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReferenceResource;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReferenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Reference::with(['category', 'publisher', 'authors', 'keywords', 'uploader']);

        // Non-authentifiés : seulement les références publiées
        if (!$request->user()) {
            $query->where('status', 'published');
        }

        // Filtres
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
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

        // Recherche par mots-clés
        if ($request->has('keyword')) {
            $query->whereHas('keywords', function ($q) use ($request) {
                $q->where('keyword', 'like', "%{$request->keyword}%");
            });
        }

        // Recherche par auteur
        if ($request->has('author')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->author}%")
                    ->orWhere('last_name', 'like', "%{$request->author}%");
            });
        }

        return ReferenceResource::collection($query->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|unique:references',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'in:fr,en,autre',
            'document_type' => 'in:livre,memoire,these,article,revue,rapport,guide,autre',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'cover_image' => 'nullable|image|max:2048',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'pages' => 'nullable|integer|min:1',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
        ]);

        $data = $request->except(['cover_image', 'file_path', 'author_ids', 'keywords']);

        // Upload cover
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        // Upload file
        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        $data['uploaded_by'] = $request->user()->id;
        $data['status'] = 'draft';

        $reference = Reference::create($data);

        // Attacher auteurs
        if ($request->has('author_ids')) {
            $reference->authors()->attach($request->author_ids);
        }

        // Ajouter mots-clés
        if ($request->has('keywords')) {
            foreach ($request->keywords as $keyword) {
                $reference->keywords()->create(['keyword' => $keyword]);
            }
        }

        return new ReferenceResource($reference->load(['category', 'publisher', 'authors', 'keywords']));
    }

    public function show(Request $request, Reference $reference)
    {
        // Incrémenter les vues
        $reference->increment('view_count');

        $load = ['category', 'publisher', 'authors', 'keywords', 'uploader'];

        // Charger les stats détaillées seulement si authentifié
        if ($request->user()) {
            $load = array_merge($load, ['downloads', 'views']);
        }

        return new ReferenceResource($reference->load($load));
    }

    public function update(Request $request, Reference $reference)
    {
        $request->validate([
            'title' => 'string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string',
            'isbn' => 'nullable|string|unique:references,isbn,' . $reference->id,
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'in:fr,en,autre',
            'document_type' => 'in:livre,memoire,these,article,revue,rapport,guide,autre',
            'category_id' => 'exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'status' => 'in:draft,published,archived',
            'pages' => 'nullable|integer|min:1',
        ]);

        $reference->update($request->all());

        return new ReferenceResource($reference->load(['category', 'publisher', 'authors', 'keywords']));
    }

    public function destroy(Reference $reference)
    {
        // Supprimer les fichiers
        if ($reference->cover_image) {
            Storage::disk('public')->delete($reference->cover_image);
        }
        if ($reference->file_path) {
            Storage::disk('public')->delete($reference->file_path);
        }

        $reference->delete();

        return response()->json(null, 204);
    }

    public function read(Request $request, Reference $reference)
    {
        if (!$reference->file_path || !Storage::disk('public')->exists($reference->file_path)) {
            return response()->json(['message' => 'Document non disponible.'], 404);
        }

        // Les visiteurs ne peuvent lire que les références publiées
        if (!$request->user() && $reference->status !== 'published') {
            return response()->json(['message' => 'Document non disponible.'], 404);
        }

        // Log de consultation
        $reference->views()->create([
            'user_id' => $request->user()?->id,
            'viewed_at' => now(),
        ]);

        $reference->increment('view_count');

        $path = Storage::disk('public')->path($reference->file_path);
        $mime = Storage::disk('public')->mimeType($reference->file_path);

        return response()->file($path, ['Content-Type' => $mime]);
    }

    public function download(Request $request, Reference $reference)
    {
        if (!$reference->file_path || !Storage::disk('public')->exists($reference->file_path)) {
            return response()->json(['message' => 'Fichier non disponible.'], 404);
        }

        // Log du téléchargement
        $reference->downloads()->create([
            'user_id' => $request->user()?->id,
            'downloaded_at' => now(),
        ]);

        $reference->increment('download_count');

        return Storage::disk('public')->download($reference->file_path);
    }
}