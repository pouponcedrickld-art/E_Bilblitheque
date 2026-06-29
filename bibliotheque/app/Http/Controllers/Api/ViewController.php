<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ViewController extends Controller
{
    // Liste des consultations (admin voit tout, user voit les siennes)
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            $query = View::with(['user', 'reference']);
        } else {
            $query = View::with('reference')
                ->where('user_id', $request->user()->id);
        }

        if ($request->has('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }
        if ($request->has('date_from')) {
            $query->where('viewed_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->has('date_to')) {
            $query->where('viewed_at', '<=', $request->date_to . ' 23:59:59');
        }

        return response()->json($query->orderBy('viewed_at', 'desc')->paginate(30));
    }

    // Détail d'une consultation (admin ou propriétaire)
    public function show(Request $request, View $view)
    {
        if ($request->user()->role !== 'admin' && $request->user()->id !== $view->user_id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($view->load(['user', 'reference']));
    }

    public function stats(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $stats = Cache::remember('views.stats', 1800, function () {
            return [
                'total_views' => View::count(),
                'today_views' => View::where('viewed_at', '>=', now()->startOfDay())->count(),
                'this_month' => View::where('viewed_at', '>=', now()->startOfMonth())->count(),
                'unique_viewers' => View::distinct('user_id')->count('user_id'),
                'top_references' => View::selectRaw('reference_id, count(*) as count')
                    ->with('reference:id,title')
                    ->groupBy('reference_id')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get(),
                'views_over_time' => View::selectRaw('DATE(viewed_at) as date, count(*) as count')
                    ->where('viewed_at', '>=', now()->subDays(30)->startOfDay())
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ];
        });

        return response()->json($stats);
    }
}