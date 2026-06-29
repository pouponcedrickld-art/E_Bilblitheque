<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::remember('categories', 3600, function () {
            return Category::all();
        });

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $data = $request->all();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $category = Category::create($data);

        Cache::forget('categories');
        Cache::forget('stats');

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('references'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $category->update($request->all());

        Cache::forget('categories');
        Cache::forget('stats');

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        Cache::forget('categories');
        Cache::forget('stats');

        return response()->json(null, 204);
    }
}