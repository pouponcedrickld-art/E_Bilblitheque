<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\View;
use Illuminate\Http\Request;

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

        // Filtres optionnels
        if ($request->has('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }
        if ($request->has('date_from')) {
            $query->whereDate('viewed_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('viewed_at', '<=', $request->date_to);
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

    // Statistiques des consultations (admin et RH uniquement)
    public function stats(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $stats = [
            'total_views' => View::count(),
            'today_views' => View::whereDate('viewed_at', today())->count(),
            'this_month' => View::whereMonth('viewed_at', now()->month)
                ->whereYear('viewed_at', now()->year)
                ->count(),
            'unique_viewers' => View::distinct('user_id')->count('user_id'),
            'top_references' => View::selectRaw('reference_id, count(*) as count')
                ->with('reference:id,title')
                ->groupBy('reference_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            // Évolution des vues sur les 30 derniers jours
            'views_over_time' => View::selectRaw('DATE(viewed_at) as date, count(*) as count')
                ->whereDate('viewed_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($stats);
    }
}