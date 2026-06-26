<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Liste toutes les catégories
    public function index()
    {
        return response()->json(Category::all());
    }

    // Crée une catégorie (name et slug uniques requis)
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

        return response()->json($category, 201);
    }

    // Détail d'une catégorie avec ses références
    public function show(Category $category)
    {
        return response()->json($category->load('references'));
    }

    // Met à jour une catégorie (ignore l'ID courant dans l'unicité)
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $category->update($request->all());

        return response()->json($category);
    }

    // Supprime une catégorie
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(null, 204);
    }
}