<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Tout le monde peut lister
    }

    public function view(User $user, Category $category): bool
    {
        return true; // Tout le monde peut voir
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    public function update(User $user, Category $category): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }
}