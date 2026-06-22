<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    // Liste tous les auteurs avec le nombre de références associées
    public function index()
    {
        return response()->json(Author::withCount('references')->get());
    }

    // Crée un auteur
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'biography' => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
        ]);

        $author = Author::create($request->all());

        return response()->json($author, 201);
    }

    // Détail d'un auteur avec ses références publiées
    public function show(Author $author)
    {
        return response()->json($author->load([
            'references' => function ($q) {
                $q->where('status', 'published');
            }
        ]));
    }

    // Met à jour un auteur
    public function update(Request $request, Author $author)
    {
        $request->validate([
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'biography' => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
        ]);

        $author->update($request->all());

        return response()->json($author);
    }

    // Supprime un auteur
    public function destroy(Author $author)
    {
        $author->delete();

        return response()->json(null, 204);
    }
}