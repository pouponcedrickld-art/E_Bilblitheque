<?php

namespace App\Policies;

use App\Models\DepositRequest;
use App\Models\User;

class DepositRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

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

    public function create(User $user): bool
    {
        return in_array($user->role, ['user', 'admin']);
    }

    public function update(User $user, DepositRequest $depositRequest): bool
    {
        // Seul le demandeur peut modifier tant que pending
        return $user->id === $depositRequest->applicant_id
            && $depositRequest->status === 'pending';
    }

    public function delete(User $user, DepositRequest $depositRequest): bool
    {
        return $user->role === 'admin'
            || ($user->id === $depositRequest->applicant_id && $depositRequest->status === 'pending');
    }

    /**
     * Responsable : approuver/refuser une demande
     */
    public function review(User $user, DepositRequest $depositRequest): bool
    {
        return $user->role === 'responsable_demande'
            && $depositRequest->assigned_manager_id === $user->id
            && $depositRequest->status === 'pending';
    }

    /**
     * Admin : actions de publication/rejet/override/second avis
     */
    public function adminAction(User $user, DepositRequest $depositRequest): bool
    {
        return $user->role === 'admin';
    }
}