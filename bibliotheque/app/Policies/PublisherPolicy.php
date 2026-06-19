<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class PublisherPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Chacun voit ses propres notifications via controller
    }

    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_demande']);
        // Seuls ceux qui génèrent des actions système/validation peuvent créer
    }

    public function update(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id; // Marquer comme lue
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id || $user->role === 'admin';
    }
}