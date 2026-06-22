<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    // Tout le monde peut lister les auteurs
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Tout le monde peut voir un auteur
    public function view(User $user, Author $author): bool
    {
        return true;
    }

    // Admin et responsable RH peuvent créer
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    // Admin et responsable RH peuvent modifier
    public function update(User $user, Author $author): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    // Seul l'admin peut supprimer
    public function delete(User $user, Author $author): bool
    {
        return $user->role === 'admin';
    }
}