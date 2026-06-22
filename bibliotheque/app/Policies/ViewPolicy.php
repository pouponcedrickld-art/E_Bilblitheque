<?php

namespace App\Policies;

use App\Models\View;
use App\Models\User;

class ViewPolicy
{
    // Tout le monde peut lister les vues
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Admin ou propriétaire peut voir une vue
    public function view(User $user, View $view): bool
    {
        return $user->role === 'admin' || $user->id === $view->user_id;
    }

    // Tout utilisateur connecté peut créer une vue
    public function create(User $user): bool
    {
        return true;
    }

    // Les vues sont immuables
    public function update(User $user, View $view): bool
    {
        return false;
    }

    // Admin peut supprimer une vue
    public function delete(User $user, View $view): bool
    {
        return $user->role === 'admin';
    }
}