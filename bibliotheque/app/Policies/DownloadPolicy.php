<?php

namespace App\Policies;

use App\Models\Download;
use App\Models\User;

class DownloadPolicy
{
    // Chacun voit ses propres téléchargements (filtré par controller)
    public function viewAny(User $user): bool
    {
        return true; // Chacun voit ses propres via controller
    }

    // Admin ou propriétaire peut voir un téléchargement
    public function view(User $user, Download $download): bool
    {
        return $user->role === 'admin' || $user->id === $download->user_id;
    }

    // Tout utilisateur connecté peut télécharger
    public function create(User $user): bool
    {
        return true; // Tout utilisateur connecté peut télécharger
    }

    // Les téléchargements sont immuables
    public function update(User $user, Download $download): bool
    {
        return false; // Immuable
    }

    // Admin peut supprimer un téléchargement
    public function delete(User $user, Download $download): bool
    {
        return $user->role === 'admin';
    }
}