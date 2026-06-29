<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KeywordResource;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class KeywordController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'keywords:' . ($request->search ?? 'all');

        $keywords = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Keyword::query();

            if ($request->has('search')) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            return KeywordResource::collection($query->orderBy('name')->paginate(50));
        });

        return $keywords;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:keywords,name',
        ]);

        $keyword = Keyword::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        Cache::forget('keywords:all');

        return new KeywordResource($keyword);
    }

    public function destroy(Keyword $keyword)
    {
        $keyword->references()->detach();
        $keyword->delete();

        Cache::forget('keywords:all');

        return response()->json(null, 204);
    }
}
