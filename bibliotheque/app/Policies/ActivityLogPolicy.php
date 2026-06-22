<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    // Admin peut lister tous les logs
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    // Admin peut voir un log spécifique
    public function view(User $user, ActivityLog $activityLog): bool
    {
        return $user->role === 'admin';
    }

    // Création automatique uniquement, pas d'accès direct
    public function create(User $user): bool
    {
        return false; // Les logs se créent automatiquement
    }

    // Les logs sont immuables
    public function update(User $user, ActivityLog $activityLog): bool
    {
        return false; // Les logs sont immuables
    }

    // Admin peut purger les logs
    public function delete(User $user, ActivityLog $activityLog): bool
    {
        return $user->role === 'admin'; // Purge possible par admin
    }
}