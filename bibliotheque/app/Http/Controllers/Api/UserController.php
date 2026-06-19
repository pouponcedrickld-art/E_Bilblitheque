<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Seul admin et responsable_rh peuvent voir la liste
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $query = User::query();

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

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        // Seul admin et responsable_rh peuvent créer
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
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
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

        return response()->json($user, 201);
    }

    public function show(Request $request, User $user)
    {
        // Admin/RH peuvent voir tout le monde, user ne voit que son profil
        if ($request->user()->role === 'user' && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($user->load([
            'references', 'depositRequests', 'assignedDepositRequests', 
            'notifications', 'activityLogs', 'downloads', 'views'
        ]));
    }

    public function update(Request $request, User $user)
    {
        // Admin peut tout modifier, RH peut modifier users et responsables, user ne modifie que son profil
        $currentUser = $request->user();

        if ($currentUser->role === 'user' && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($currentUser->role === 'responsable_rh' && in_array($user->role, ['admin'])) {
            return response()->json(['message' => 'Vous ne pouvez pas modifier un admin.'], 403);
        }

        $rules = [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'phone' => 'nullable|string|max:20',
        ];

        // Seul admin et RH peuvent changer le rôle et le statut
        if (in_array($currentUser->role, ['admin', 'responsable_rh'])) {
            $rules['role'] = [Rule::in(['admin', 'responsable_rh', 'responsable_demande', 'user'])];
            $rules['status'] = [Rule::in(['active', 'inactive', 'suspended'])];
        }

        // Seul l'user lui-même ou un admin peut changer le password
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

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        // Seul admin peut supprimer, et ne peut pas se supprimer lui-même
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Activer un compte
     */
    public function activate(Request $request, User $user)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $user->update(['status' => 'active']);

        return response()->json($user);
    }

    /**
     * Suspendre un compte
     */
    public function suspend(Request $request, User $user)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous suspendre vous-même.'], 422);
        }

        $user->update(['status' => 'suspended']);

        return response()->json($user);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request, User $user)
    {
        if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $newPassword = \Illuminate\Support\Str::random(12);

        $user->update([
            'password' => Hash::make($newPassword),
            'email_verified_at' => null,
        ]);

        // TODO: Envoyer email avec le nouveau mot de passe

        return response()->json([
            'message' => 'Mot de passe réinitialisé.',
            'temporary_password' => $newPassword, // À retirer en production, envoyer par email
        ]);
    }
}