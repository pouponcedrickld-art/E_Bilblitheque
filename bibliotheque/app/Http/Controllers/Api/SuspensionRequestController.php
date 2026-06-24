<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\SuspensionRequest;
use App\Models\User;
use Illuminate\Http\Request;

class SuspensionRequestController extends Controller
{
    // Liste des demandes de suspension
    public function index(Request $request)
    {
        $query = SuspensionRequest::with(['user', 'requester', 'reviewer']);

        // RH ne voit que ses propres demandes
        if ($request->user()->role === 'responsable_rh') {
            $query->where('requested_by', $request->user()->id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    // Crée une demande de suspension (RH uniquement)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $target = User::findOrFail($request->user_id);

        // Ne pas pouvoir demander la suspension d'un admin
        if ($target->role === 'admin') {
            return response()->json(['message' => 'Vous ne pouvez pas demander la suspension d\'un administrateur.'], 422);
        }

        // Vérifier qu'il n'y a pas déjà une demande en attente pour cet utilisateur
        $pending = SuspensionRequest::where('user_id', $request->user_id)
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            return response()->json(['message' => 'Une demande de suspension est déjà en attente pour cet utilisateur.'], 422);
        }

        $suspensionRequest = SuspensionRequest::create([
            'user_id' => $request->user_id,
            'requested_by' => $request->user()->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json($suspensionRequest->load(['user', 'requester']), 201);
    }

    // Approuve une demande de suspension (Admin uniquement)
    public function approve(Request $request, SuspensionRequest $suspensionRequest)
    {
        if ($suspensionRequest->status !== 'pending') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $suspensionRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        // Suspendre l'utilisateur
        $suspensionRequest->user->update(['status' => 'suspended']);

        // Notifier le RH
        Notification::create([
            'user_id' => $suspensionRequest->requested_by,
            'title' => 'Suspension approuvée',
            'message' => "La suspension de {$suspensionRequest->user->full_name} a été approuvée.",
            'type' => 'suspension_approved',
            'is_read' => false,
        ]);

        return response()->json($suspensionRequest->load(['user', 'requester', 'reviewer']));
    }

    // Rejette une demande de suspension (Admin uniquement)
    public function reject(Request $request, SuspensionRequest $suspensionRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        if ($suspensionRequest->status !== 'pending') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $suspensionRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notifier le RH
        Notification::create([
            'user_id' => $suspensionRequest->requested_by,
            'title' => 'Suspension refusée',
            'message' => "La suspension de {$suspensionRequest->user->full_name} a été refusée. Motif : {$suspensionRequest->rejection_reason}",
            'type' => 'suspension_rejected',
            'is_read' => false,
        ]);

        return response()->json($suspensionRequest->load(['user', 'requester', 'reviewer']));
    }
}
