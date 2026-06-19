<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Author $author): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    public function update(User $user, Author $author): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    public function delete(User $user, Author $author): bool
    {
        return $user->role === 'admin';
    }
}