<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class PublisherPolicy
{
    // Chacun voit ses propres notifications (filtré par controller)
    public function viewAny(User $user): bool
    {
        return true; // Chacun voit ses propres notifications via controller
    }

    // Seul le destinataire peut voir sa notification
    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    // Admin et responsable demande peuvent créer des notifications
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_demande']);
        // Seuls ceux qui génèrent des actions système/validation peuvent créer
    }

    // Le destinataire peut marquer sa notification comme lue
    public function update(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id; // Marquer comme lue
    }

    // Le destinataire ou l'admin peut supprimer une notification
    public function delete(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id || $user->role === 'admin';
    }
}