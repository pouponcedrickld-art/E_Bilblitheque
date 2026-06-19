<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, ActivityLog $activityLog): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return false; // Les logs se créent automatiquement
    }

    public function update(User $user, ActivityLog $activityLog): bool
    {
        return false; // Les logs sont immuables
    }

    public function delete(User $user, ActivityLog $activityLog): bool
    {
        return $user->role === 'admin'; // Purge possible par admin
    }
}