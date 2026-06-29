<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Cache::remember('authors', 3600, function () {
            return Author::withCount('references')->get();
        });

        return response()->json($authors);
    }

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

        Cache::forget('authors');
        Cache::forget('stats');

        return response()->json($author, 201);
    }

    public function show(Author $author)
    {
        return response()->json($author->load([
            'references' => function ($q) {
                $q->where('status', 'published');
            }
        ]));
    }

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

        Cache::forget('authors');
        Cache::forget('stats');

        return response()->json($author);
    }

    public function destroy(Author $author)
    {
        $author->delete();

        Cache::forget('authors');
        Cache::forget('stats');

        return response()->json(null, 204);
    }
}