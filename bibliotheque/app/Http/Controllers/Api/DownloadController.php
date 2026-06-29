<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DownloadController extends Controller
{
    // Liste des téléchargements (admin voit tout, user voit les siens)
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            $query = Download::with(['user', 'reference']);
        } else {
            $query = Download::with('reference')
                ->where('user_id', $request->user()->id);
        }

        if ($request->has('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }
        if ($request->has('date_from')) {
            $query->where('downloaded_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->has('date_to')) {
            $query->where('downloaded_at', '<=', $request->date_to . ' 23:59:59');
        }

        return response()->json($query->orderBy('downloaded_at', 'desc')->paginate(30));
    }

    // Détail d'un téléchargement (admin ou propriétaire)
    public function show(Request $request, Download $download)
    {
        if ($request->user()->role !== 'admin' && $request->user()->id !== $download->user_id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($download->load(['user', 'reference']));
    }

    public function stats(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $stats = Cache::remember('downloads.stats', 1800, function () {
            return [
                'total_downloads' => Download::count(),
                'today_downloads' => Download::where('downloaded_at', '>=', now()->startOfDay())->count(),
                'this_month' => Download::where('downloaded_at', '>=', now()->startOfMonth())->count(),
                'top_references' => Download::selectRaw('reference_id, count(*) as count')
                    ->with('reference:id,title')
                    ->groupBy('reference_id')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get(),
                'top_users' => Download::selectRaw('user_id, count(*) as count')
                    ->with('user:id,first_name,last_name,email')
                    ->groupBy('user_id')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get(),
            ];
        });

        return response()->json($stats);
    }
}