<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KeywordResource;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KeywordController extends Controller
{
    // Liste les mots-clés avec recherche par nom
    public function index(Request $request)
    {
        $query = Keyword::query();

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return KeywordResource::collection($query->orderBy('name')->paginate(50));
    }

    // Crée un mot-clé avec slug généré automatiquement
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:keywords,name',
        ]);

        $keyword = Keyword::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return new KeywordResource($keyword);
    }

    // Supprime un mot-clé après avoir détaché ses références
    public function destroy(Keyword $keyword)
    {
        $keyword->references()->detach();
        $keyword->delete();

        return response()->json(null, 204);
    }
}
