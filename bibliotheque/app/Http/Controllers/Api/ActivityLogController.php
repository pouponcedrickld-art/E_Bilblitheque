<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $query = ActivityLog::with('user');

        // Filtre par utilisateur
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtre par action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filtre par table cible
        if ($request->has('target_table')) {
            $query->where('target_table', $request->target_table);
        }

        // Filtre par ID cible
        if ($request->has('target_id')) {
            $query->where('target_id', $request->target_id);
        }

        // Recherche plein texte
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('target_table', 'like', "%{$search}%");
            });
        }

        // Période prédéfinie
        if ($request->has('period')) {
            $query = $this->applyPeriodFilter($query, $request->period);
        } else {
            // Filtre date personnalisé (utilisé si period n'est pas fourni)
            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
            }
            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }
        }

        // Tri personnalisé
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'user_id', 'action', 'target_table'];

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        $logs = $query->paginate($request->per_page ?? 50);

        $meta = [
            'available_actions' => ActivityLog::select('action')->distinct()->orderBy('action')->get()
                ->map(fn($a) => ['value' => $a->action, 'label' => $this->actionLabel($a->action)]),
            'available_tables' => ActivityLog::select('target_table')->distinct()->orderBy('target_table')->get()
                ->map(fn($t) => ['value' => $t->target_table, 'label' => $this->tableLabel($t->target_table)]),
            'date_range' => $this->getDateRange(),
        ];

        return ActivityLogResource::collection($logs)->additional(['meta' => $meta]);
    }

    public function filters(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $cacheKey = 'activity_logs.filters';

        $filters = Cache::remember($cacheKey, 3600, function () {
            $users = User::whereIn('id', ActivityLog::select('user_id')->distinct()->pluck('user_id'))
                ->select('id', 'first_name', 'last_name', 'email')
                ->orderBy('first_name')
                ->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'full_name' => $u->first_name . ' ' . $u->last_name,
                    'email' => $u->email,
                ])
                ->values();

            return [
                'available_actions' => ActivityLog::select('action')->distinct()->orderBy('action')->get()
                    ->map(fn($a) => ['value' => $a->action, 'label' => $this->actionLabel($a->action)]),
                'available_tables' => ActivityLog::select('target_table')->distinct()->orderBy('target_table')->get()
                    ->map(fn($t) => ['value' => $t->target_table, 'label' => $this->tableLabel($t->target_table)]),
                'users' => $users,
                'date_range' => $this->getDateRange(),
            ];
        });

        return response()->json($filters);
    }

    public function show(Request $request, ActivityLog $activityLog)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return new ActivityLogResource($activityLog->load('user'));
    }

    public function stats(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $stats = Cache::remember('activity_logs.stats', 1800, function () {
            return [
                'total_logs' => ActivityLog::count(),
                'today_logs' => ActivityLog::where('created_at', '>=', now()->startOfDay())->count(),
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
        });

        return response()->json($stats);
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'restored' => 'Restauration',
            default => $action,
        };
    }

    private function tableLabel(string $table): string
    {
        return match ($table) {
            'users' => 'Utilisateurs',
            'references' => 'Références',
            'categories' => 'Catégories',
            'authors' => 'Auteurs',
            'publishers' => 'Éditeurs',
            'deposit_requests' => 'Demandes de dépôt',
            'deposit_request_reviews' => 'Avis de validation',
            'notifications' => 'Notifications',
            default => $table,
        };
    }

    private function applyPeriodFilter($query, string $period)
    {
        return match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            'last_7_days' => $query->where('created_at', '>=', now()->subDays(7)->startOfDay()),
            'last_30_days' => $query->where('created_at', '>=', now()->subDays(30)->startOfDay()),
            'this_month' => $query->where('created_at', '>=', now()->startOfMonth()),
            'last_month' => $query->where('created_at', '>=', now()->subMonth()->startOfMonth())
                ->where('created_at', '<=', now()->subMonth()->endOfMonth()),
            default => $query,
        };
    }

    private function getDateRange(): array
    {
        $first = ActivityLog::orderBy('created_at')->value('created_at');
        $last = ActivityLog::orderByDesc('created_at')->value('created_at');

        return [
            'first' => $first,
            'last' => $last,
        ];
    }
}
