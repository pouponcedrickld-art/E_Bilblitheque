<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'action' => $this->action,
            'action_label' => $this->actionLabel($this->action),
            'target_table' => $this->target_table,
            'target_table_label' => $this->tableLabel($this->target_table),
            'target_id' => $this->target_id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'user_agent_summary' => $this->parseUserAgent($this->user_agent),
            'created_at' => $this->created_at,
            'created_at_diff' => $this->created_at?->diffForHumans(),
        ];
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

    private function parseUserAgent(?string $ua): array
    {
        if (!$ua) {
            return ['browser' => null, 'platform' => null];
        }

        $browser = 'Inconnu';
        $platform = 'Inconnu';

        if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edg')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'Edg')) {
            $browser = 'Edge';
        }

        if (str_contains($ua, 'Windows')) {
            $platform = 'Windows';
        } elseif (str_contains($ua, 'Mac')) {
            $platform = 'macOS';
        } elseif (str_contains($ua, 'Linux')) {
            $platform = 'Linux';
        } elseif (str_contains($ua, 'Android')) {
            $platform = 'Android';
        } elseif (str_contains($ua, 'iOS') || str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $platform = 'iOS';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
        ];
    }
}
