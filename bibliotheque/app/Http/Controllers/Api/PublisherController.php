<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    public function index()
    {
        return response()->json(Publisher::withCount('references')->get());
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

        return response()->json($publisher);
    }

    public function destroy(Publisher $publisher)
    {
        $publisher->delete();

        return response()->json(null, 204);
    }
}