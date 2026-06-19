<?php

namespace App\Policies;

use App\Models\Download;
use App\Models\User;

class DownloadPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Chacun voit ses propres via controller
    }

    public function view(User $user, Download $download): bool
    {
        return $user->role === 'admin' || $user->id === $download->user_id;
    }

    public function create(User $user): bool
    {
        return true; // Tout utilisateur connecté peut télécharger
    }

    public function update(User $user, Download $download): bool
    {
        return false; // Immuable
    }

    public function delete(User $user, Download $download): bool
    {
        return $user->role === 'admin';
    }
}