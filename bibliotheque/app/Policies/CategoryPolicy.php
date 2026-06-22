<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    // Tout le monde peut lister les catégories
    public function viewAny(User $user): bool
    {
        return true; // Tout le monde peut lister
    }

    // Tout le monde peut voir une catégorie
    public function view(User $user, Category $category): bool
    {
        return true; // Tout le monde peut voir
    }

    // Admin et responsable RH peuvent créer
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    // Admin et responsable RH peuvent modifier
    public function update(User $user, Category $category): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    // Seul l'admin peut supprimer
    public function delete(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }
}