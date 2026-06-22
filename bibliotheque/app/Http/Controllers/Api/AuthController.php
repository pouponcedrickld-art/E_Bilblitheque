<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Inscription avec rôle 'user' par défaut et statut 'pending_validation'
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'pending_validation',
        ]);

        return response()->json([
            'message' => 'Votre compte a été créé. Il doit être validé par un administrateur avant que vous puissiez faire une demande de dépôt.',
        ], 201);
    }

    // Authentification avec vérification du mot de passe et du statut du compte
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        if (in_array($user->status, ['inactive', 'suspended'])) {
            return response()->json(['message' => 'Compte suspendu ou inactif.'], 403);
        }

        Auth::login($user);

        // Met à jour la date de dernière connexion
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'message' => 'Connexion réussie.',
            'user' => new UserResource($user),
        ]);
    }

    // Déconnexion avec invalidation de session
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // Retourne le profil de l'utilisateur connecté avec ses relations
    public function me(Request $request): JsonResponse
    {
        return response()->json(
            new UserResource($request->user()->load([
                'references',
                'depositRequests',
                'notifications',
            ]))
        );
    }

    // Met à jour le profil ou le mot de passe de l'utilisateur connecté
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'phone' => 'nullable|string|max:20',
        ];

        if ($request->has('current_password') || $request->has('new_password')) {
            $rules['current_password'] = 'required_with:new_password|string';
            $rules['new_password'] = 'required_with:current_password|string|min:6|regex:/[A-Z]/|regex:/[0-9]/|confirmed';

            $request->validate($rules);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Mot de passe actuel incorrect.',
                    'errors' => ['current_password' => ['Le mot de passe actuel est incorrect.']],
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
        } else {
            $request->validate($rules);
        }

        $user->fill($request->only(['first_name', 'last_name', 'phone']));
        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => new UserResource($user),
        ]);
    }
}
