<?php

namespace App\Policies;

use App\Models\View;
use App\Models\User;

class ViewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, View $view): bool
    {
        return $user->role === 'admin' || $user->id === $view->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, View $view): bool
    {
        return false;
    }

    public function delete(User $user, View $view): bool
    {
        return $user->role === 'admin';
    }
}