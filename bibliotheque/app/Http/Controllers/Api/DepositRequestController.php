<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\DepositRequestReview;
use App\Models\Notification;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = DepositRequest::with(['applicant', 'assignedManager', 'reviews']);

        // Filtre par rôle
        if ($request->user()->role === 'responsable_demande') {
            $query->where('assigned_manager_id', $request->user()->id)
                  ->orWhereHas('reviews', function ($q) use ($request) {
                      $q->where('reviewer_id', $request->user()->id);
                  });
        } elseif ($request->user()->role === 'user') {
            $query->where('applicant_id', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'proposed_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = [
            'applicant_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending',
        ];

        if ($request->hasFile('proposed_file')) {
            $data['proposed_file'] = $request->file('proposed_file')->store('deposits', 'public');
        }

        // Assigner un responsable au hasard
        $manager = \App\Models\User::where('role', 'responsable_demande')
            ->where('status', 'active')
            ->inRandomOrder()
            ->first();

        if ($manager) {
            $data['assigned_manager_id'] = $manager->id;
        }

        $depositRequest = DepositRequest::create($data);

        // Notifier le responsable
        if ($manager) {
            Notification::create([
                'user_id' => $manager->id,
                'title' => 'Nouvelle demande de dépôt',
                'message' => "Une nouvelle demande \"{$depositRequest->title}\" vous a été assignée.",
                'type' => 'validation',
            ]);
        }

        return response()->json($depositRequest->load(['applicant', 'assignedManager']), 201);
    }

    public function show(DepositRequest $depositRequest)
    {
        $this->authorize('view', $depositRequest);

        return response()->json($depositRequest->load([
            'applicant', 'assignedManager', 'reviews.reviewer'
        ]));
    }

    public function update(Request $request, DepositRequest $depositRequest)
    {
        $this->authorize('update', $depositRequest);

        $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        $depositRequest->update($request->all());

        return response()->json($depositRequest);
    }

    public function destroy(DepositRequest $depositRequest)
    {
        $this->authorize('delete', $depositRequest);

        $depositRequest->delete();

        return response()->json(null, 204);
    }

    /*
    |--------------------------------------------------------------------------
    | Actions du workflow de validation
    |--------------------------------------------------------------------------
    */

    /**
     * Responsable : valider une demande
     */
    public function approveByManager(Request $request, DepositRequest $depositRequest)
    {
        $request->validate(['justification' => 'nullable|string']);

        if ($depositRequest->status !== 'pending') {
            return response()->json(['message' => 'Cette demande ne peut plus être modifiée.'], 422);
        }

        DB::transaction(function () use ($request, $depositRequest) {
            // Créer l'avis
            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'responsable_demande',
                'decision' => 'approved',
                'justification' => $request->justification,
            ]);

            $depositRequest->update(['status' => 'approved_by_manager']);

            // Notifier l'admin
            $admins = \App\Models\User::where('role', 'admin')->where('status', 'active')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Demande validée par responsable',
                    'message' => "La demande \"{$depositRequest->title}\" a été validée par le responsable. En attente de publication.",
                    'type' => 'validation',
                ]);
            }
        });

        return response()->json($depositRequest->load('reviews'));
    }

    /**
     * Responsable : refuser une demande (avec justification obligatoire)
     */
    public function rejectByManager(Request $request, DepositRequest $depositRequest)
    {
        $request->validate(['justification' => 'required|string|min:10']);

        if ($depositRequest->status !== 'pending') {
            return response()->json(['message' => 'Cette demande ne peut plus être modifiée.'], 422);
        }

        DB::transaction(function () use ($request, $depositRequest) {
            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'responsable_demande',
                'decision' => 'rejected',
                'justification' => $request->justification,
            ]);

            $depositRequest->update(['status' => 'rejected_by_manager']);

            // Notifier l'admin
            $admins = \App\Models\User::where('role', 'admin')->where('status', 'active')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Demande refusée par responsable',
                    'message' => "La demande \"{$depositRequest->title}\" a été refusée. Justification : " . substr($request->justification, 0, 100) . '...',
                    'type' => 'validation',
                ]);
            }
        });

        return response()->json($depositRequest->load('reviews'));
    }

    /**
     * Admin : publier une demande validée
     */
    public function publish(Request $request, DepositRequest $depositRequest)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($depositRequest->status !== 'approved_by_manager') {
            return response()->json(['message' => 'La demande doit être validée par un responsable avant publication.'], 422);
        }

        DB::transaction(function () use ($depositRequest) {
            // Créer la référence
            $reference = Reference::create([
                'title' => $depositRequest->title,
                'abstract' => $depositRequest->description,
                'file_path' => $depositRequest->proposed_file,
                'uploaded_by' => $depositRequest->applicant_id,
                'status' => 'published',
            ]);

            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => auth()->id(),
                'reviewer_role' => 'admin',
                'decision' => 'approved',
                'justification' => 'Publication automatique après validation du responsable.',
            ]);

            $depositRequest->update(['status' => 'published']);

            // Notifier le demandeur
            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Votre référence a été publiée',
                'message' => "Votre demande \"{$depositRequest->title}\" a été publiée avec succès.",
                'type' => 'publication',
            ]);
        });

        return response()->json($depositRequest->load('reviews'));
    }

    /**
     * Admin : rejeter définitivement
     */
    public function rejectByAdmin(Request $request, DepositRequest $depositRequest)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate(['justification' => 'required|string|min:10']);

        DB::transaction(function () use ($request, $depositRequest) {
            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'admin',
                'decision' => 'rejected',
                'justification' => $request->justification,
            ]);

            $depositRequest->update(['status' => 'rejected']);

            // Notifier le demandeur
            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Votre demande a été rejetée',
                'message' => "Votre demande \"{$depositRequest->title}\" a été rejetée. Motif : " . substr($request->justification, 0, 100),
                'type' => 'validation',
            ]);
        });

        return response()->json($depositRequest->load('reviews'));
    }

    /**
     * Admin : passer outre un refus et publier
     */
    public function overrideReject(Request $request, DepositRequest $depositRequest)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($depositRequest->status !== 'rejected_by_manager') {
            return response()->json(['message' => 'Cette demande n\'a pas été refusée par un responsable.'], 422);
        }

        $request->validate(['justification' => 'required|string|min:10']);

        DB::transaction(function () use ($request, $depositRequest) {
            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'admin',
                'decision' => 'override',
                'justification' => $request->justification,
            ]);

            $reference = Reference::create([
                'title' => $depositRequest->title,
                'abstract' => $depositRequest->description,
                'file_path' => $depositRequest->proposed_file,
                'uploaded_by' => $depositRequest->applicant_id,
                'status' => 'published',
            ]);

            $depositRequest->update(['status' => 'published']);

            // Notifier le demandeur ET le responsable
            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Votre référence a été publiée (override admin)',
                'message' => "Votre demande \"{$depositRequest->title}\" a été publiée malgré le refus initial.",
                'type' => 'publication',
            ]);

            if ($depositRequest->assigned_manager_id) {
                Notification::create([
                    'user_id' => $depositRequest->assigned_manager_id,
                    'title' => 'Override sur une de vos décisions',
                    'message' => "Votre refus sur \"{$depositRequest->title}\" a été invalidé par l'administrateur.",
                    'type' => 'information',
                ]);
            }
        });

        return response()->json($depositRequest->load('reviews'));
    }

    /**
     * Admin : demander un second avis
     */
    public function requestSecondReview(Request $request, DepositRequest $depositRequest)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'new_manager_id' => 'required|exists:users,id',
            'justification' => 'nullable|string',
        ]);

        if (!in_array($depositRequest->status, ['rejected_by_manager', 'approved_by_manager'])) {
            return response()->json(['message' => 'Demande non éligible pour un second avis.'], 422);
        }

        DB::transaction(function () use ($request, $depositRequest) {
            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'admin',
                'decision' => 'second_opinion_requested',
                'justification' => $request->justification ?? 'Demande de second avis.',
            ]);

            $depositRequest->update([
                'status' => 'second_review',
                'assigned_manager_id' => $request->new_manager_id,
            ]);

            // Notifier le nouveau responsable
            Notification::create([
                'user_id' => $request->new_manager_id,
                'title' => 'Second avis demandé',
                'message' => "Vous êtes sollicité pour un second avis sur \"{$depositRequest->title}\".",
                'type' => 'validation',
            ]);
        });

        return response()->json($depositRequest->load('reviews'));
    }
}