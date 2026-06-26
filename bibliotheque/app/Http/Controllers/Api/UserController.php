<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\SuspensionRequest;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Liste paginée des utilisateurs avec filtres (rôle, statut, recherche)
    public function index(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $query = User::query();

        // Filtres optionnels
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        // Ajoute has_pending_suspension à chaque utilisateur
        $pendingIds = SuspensionRequest::where('status', 'pending')
            ->pluck('user_id')
            ->toArray();

        $users->getCollection()->transform(function ($user) use ($pendingIds) {
            $user->has_pending_suspension = in_array($user->id, $pendingIds);
            return $user;
        });

        return UserResource::collection($users);
    }

    // Crée un utilisateur avec rôle (admin/RH uniquement)
    public function store(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'responsable_rh', 'responsable_demande', 'user'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended', 'pending_validation'])],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status ?? 'active',
        ]);

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    // Détail d'un utilisateur avec toutes ses relations
    public function show(Request $request, User $user)
    {
        // Un utilisateur standard ne voit que son propre profil
        if ($request->user()->role === 'user' && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return new UserResource($user->load([
            'references', 'depositRequests', 'assignedDepositRequests', 
            'notifications', 'activityLogs', 'downloads', 'views'
        ]));
    }

    // Met à jour un utilisateur (rôle, statut, password) avec vérifications de droits
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser->role === 'user' && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Un RH ne peut pas modifier un admin
        if ($currentUser->role === 'responsable_rh' && in_array($user->role, ['admin'])) {
            return response()->json(['message' => 'Vous ne pouvez pas modifier un admin.'], 403);
        }

        $rules = [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'phone' => 'nullable|string|max:20',
        ];

        // Seuls admin et RH peuvent changer rôle et statut
        if (in_array($currentUser->role, ['admin', 'responsable_rh'])) {
            $rules['role'] = [Rule::in(['admin', 'responsable_rh', 'responsable_demande', 'user'])];
            $rules['status'] = [Rule::in(['active', 'inactive', 'suspended', 'pending_validation'])];
        }

        // Changement de mot de passe (vérifie l'ancien si c'est l'utilisateur lui-même)
        if ($request->has('password')) {
            $rules['password'] = 'string|min:8';
            $rules['current_password'] = 'required_with:password';
            
            if ($currentUser->id === $user->id && !Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
            }
        }

        $request->validate($rules);

        $data = $request->except(['password', 'current_password']);
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return new UserResource($user);
    }

    // Supprime un utilisateur (admin/RH, pas soi-même, RH ne peut pas supprimer admin)
    public function destroy(Request $request, User $user)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($request->user()->role === 'responsable_rh' && $user->role === 'admin') {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer un administrateur.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    // Active un compte utilisateur (admin/RH)
    public function activate(Request $request, User $user)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $user->update(['status' => 'active']);

        return new UserResource($user);
    }

    // Suspend un compte utilisateur (admin/RH, pas soi-même)
    public function suspend(Request $request, User $user)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous suspendre vous-même.'], 422);
        }

        $user->update(['status' => 'suspended']);

        return new UserResource($user);
    }

    // Réinitialise le mot de passe et envoie un email avec le nouveau mot de passe
    public function resetPassword(Request $request, User $user)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $newPassword = Str::random(12);

        $user->update([
            'password' => Hash::make($newPassword),
            'email_verified_at' => null,
        ]);

        $user->notify(new PasswordResetNotification($newPassword));

        return response()->json([
            'message' => 'Mot de passe réinitialisé. Un email a été envoyé à l\'utilisateur.',
        ]);
    }
}