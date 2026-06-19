<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Download;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        // Admin voit tout, user voit ses propres téléchargements
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
            $query->whereDate('downloaded_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('downloaded_at', '<=', $request->date_to);
        }

        return response()->json($query->orderBy('downloaded_at', 'desc')->paginate(30));
    }

    public function show(Request $request, Download $download)
    {
        if ($request->user()->role !== 'admin' && $request->user()->id !== $download->user_id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($download->load(['user', 'reference']));
    }

    /**
     * Statistiques des téléchargements
     */
    public function stats(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $stats = [
            'total_downloads' => Download::count(),
            'today_downloads' => Download::whereDate('downloaded_at', today())->count(),
            'this_month' => Download::whereMonth('downloaded_at', now()->month)
                ->whereYear('downloaded_at', now()->year)
                ->count(),
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

        return response()->json($stats);
    }
}