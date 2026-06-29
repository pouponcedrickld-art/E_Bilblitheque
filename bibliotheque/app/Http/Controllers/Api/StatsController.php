<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Download;
use App\Models\Reference;
use App\Models\View;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('stats', 3600, function () {
            return [
                'total_references' => Reference::where('status', 'published')->count(),
                'total_categories' => Category::where('status', 'active')->count(),
                'total_authors' => Author::count(),
                'total_downloads' => Download::count(),
                'total_views' => View::count(),
            ];
        });

        return response()->json($stats);
    }
}
