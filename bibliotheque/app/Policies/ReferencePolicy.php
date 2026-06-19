<?php

namespace App\Policies;

use App\Models\Reference;
use App\Models\User;

class ReferencePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Visiteurs aussi via API sans auth
    }

    public function view(User $user, Reference $reference): bool
    {
        // Publiée = tout le monde voit, brouillon/archivé = seul admin/uploader
        if ($reference->status === 'published') {
            return true;
        }

        return $user->role === 'admin' || $user->id === $reference->uploaded_by;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh', 'responsable_demande', 'user']);
    }

    public function update(User $user, Reference $reference): bool
    {
        return $user->role === 'admin' || $user->id === $reference->uploaded_by;
    }

    public function delete(User $user, Reference $reference): bool
    {
        return $user->role === 'admin';
    }

    public function download(User $user, Reference $reference): bool
    {
        return $reference->status === 'published' && $reference->file_path !== null;
    }
}