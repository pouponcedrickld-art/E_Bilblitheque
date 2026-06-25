<?php

namespace App\Policies;

use App\Models\Reference;
use App\Models\User;

class ReferencePolicy
{
    // Tout le monde peut lister les références (y compris visiteurs)
    public function viewAny(User $user): bool
    {
        return true; // Visiteurs aussi via API sans auth
    }

    // Publiée = accès public, brouillon/archivé = admin ou uploader seulement
    public function view(User $user, Reference $reference): bool
    {
        // Publiée = tout le monde voit, brouillon/archivé = seul admin/uploader
        if ($reference->status === 'published') {
            return true;
        }

        return $user->role === 'admin' || $user->id === $reference->uploaded_by;
    }

    // Plusieurs rôles peuvent créer des références
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh', 'responsable_demande', 'user']);
    }

    // Admin ou uploader peut modifier la référence
    public function update(User $user, Reference $reference): bool
    {
        return $user->role === 'admin' || $user->id === $reference->uploaded_by;
    }

    // Seul l'admin peut supprimer une référence
    public function delete(User $user, Reference $reference): bool
    {
        return $user->role === 'admin';
    }

    // Téléchargement autorisé si propriétaire OU admin, ou si allow_download est vrai (publiée avec fichier)
    public function download(User $user, Reference $reference): bool
    {
        return $user->id === $reference->uploaded_by
            || $user->role === 'admin'
            || ($reference->status === 'published'
                && $reference->file_path !== null
                && $reference->allow_download === true);
    }
}