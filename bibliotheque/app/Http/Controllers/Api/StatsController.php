<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Download;
use App\Models\Reference;
use App\Models\View;

class StatsController extends Controller
{
    // Retourne les statistiques générales de la bibliothèque
    public function index()
    {
        return response()->json([
            'total_references' => Reference::where('status', 'published')->count(),
            'total_categories' => Category::where('status', 'active')->count(),
            'total_authors' => Author::count(),
            'total_downloads' => Download::count(),
            'total_views' => View::count(),
        ]);
    }
}
