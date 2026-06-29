<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = Cache::remember('publishers', 3600, function () {
            return Publisher::withCount('references')->get();
        });

        return response()->json($publishers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:publishers',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
        ]);

        $publisher = Publisher::create($request->all());

        Cache::forget('publishers');
        Cache::forget('stats');

        return response()->json($publisher, 201);
    }

    public function show(Publisher $publisher)
    {
        return response()->json($publisher->load([
            'references' => function ($q) {
                $q->where('status', 'published');
            }
        ]));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $request->validate([
            'name' => 'string|max:255|unique:publishers,name,' . $publisher->id,
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
        ]);

        $publisher->update($request->all());

        Cache::forget('publishers');
        Cache::forget('stats');

        return response()->json($publisher);
    }

    public function destroy(Publisher $publisher)
    {
        $publisher->delete();

        Cache::forget('publishers');
        Cache::forget('stats');

        return response()->json(null, 204);
    }
}