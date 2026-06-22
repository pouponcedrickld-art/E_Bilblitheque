<?php

namespace App\Policies;

use App\Models\DepositRequest;
use App\Models\User;

class DepositRequestPolicy
{
    // Tout le monde peut lister les demandes
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Visibilité restreinte selon le rôle (admin, responsable, demandeur)
    public function view(User $user, DepositRequest $depositRequest): bool
    {
        // Admin voit tout
        if ($user->role === 'admin') {
            return true;
        }

        // Responsable demande voit celles qui lui sont assignées + celles où il a donné un avis
        if ($user->role === 'responsable_demande') {
            return $depositRequest->assigned_manager_id === $user->id
                || $depositRequest->reviews()->where('reviewer_id', $user->id)->exists();
        }

        // User voit seulement ses propres demandes
        return $user->id === $depositRequest->applicant_id;
    }

    // User et admin peuvent créer une demande
    public function create(User $user): bool
    {
        return in_array($user->role, ['user', 'admin']);
    }

    // Le demandeur peut modifier tant que la demande est en attente
    public function update(User $user, DepositRequest $depositRequest): bool
    {
        // Seul le demandeur peut modifier tant que pending
        return $user->id === $depositRequest->applicant_id
            && $depositRequest->status === 'pending';
    }

    // Admin ou demandeur (si en attente) peuvent supprimer
    public function delete(User $user, DepositRequest $depositRequest): bool
    {
        return $user->role === 'admin'
            || ($user->id === $depositRequest->applicant_id && $depositRequest->status === 'pending');
    }

    // Le responsable assigné peut approuver ou refuser une demande en attente
    /**
     * Responsable : approuver/refuser une demande
     */
    public function review(User $user, DepositRequest $depositRequest): bool
    {
        return $user->role === 'responsable_demande'
            && $depositRequest->assigned_manager_id === $user->id
            && $depositRequest->status === 'pending';
    }

    // Admin peut publier, rejeter, outrepasser ou donner un second avis
    /**
     * Admin : actions de publication/rejet/override/second avis
     */
    public function adminAction(User $user, DepositRequest $depositRequest): bool
    {
        return $user->role === 'admin';
    }
}