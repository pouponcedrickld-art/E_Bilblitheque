<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // Liste paginée des logs d'activité (admin uniquement) avec filtres
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Filtres optionnels
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        if ($request->has('target_table')) {
            $query->where('target_table', $request->target_table);
        }
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return response()->json($query->paginate(50));
    }

    // Détail d'un log d'activité (admin uniquement)
    public function show(Request $request, ActivityLog $activityLog)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($activityLog->load('user'));
    }

    // Statistiques des activités (admin uniquement)
    public function stats(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $stats = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::whereDate('created_at', today())->count(),
            'actions_breakdown' => ActivityLog::selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->get(),
            'tables_breakdown' => ActivityLog::selectRaw('target_table, count(*) as count')
                ->groupBy('target_table')
                ->orderByDesc('count')
                ->get(),
            'most_active_users' => ActivityLog::selectRaw('user_id, count(*) as count')
                ->with('user:id,first_name,last_name,email')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }
}