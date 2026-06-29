<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepositRequestRequest;
use App\Http\Resources\DepositRequestResource;
use App\Models\DepositRequest;
use App\Models\DepositRequestReview;
use App\Models\Notification;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DepositRequestController extends Controller
{
    // Liste des demandes de dépôt filtrée par rôle : admin voit tout, responsable voit ses assignations, user voit ses demandes
    public function index(Request $request)
    {
        $query = DepositRequest::with(['applicant', 'assignedManager', 'reviews']);

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

        return DepositRequestResource::collection($query->latest()->paginate(15));
    }

    // Crée une demande de dépôt et assigne un responsable au hasard
    public function store(StoreDepositRequestRequest $request)
    {
        $this->authorize('create', DepositRequest::class);

        $data = [
            'applicant_id' => $request->user()->id,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'abstract' => $request->abstract,
            'description' => $request->description,
            'isbn' => $request->isbn,
            'publication_year' => $request->filled('publication_year') ? $request->publication_year : null,
            'language' => $request->language ?? 'fr',
            'document_type_id' => $request->filled('document_type_id') ? $request->document_type_id : null,
            'category_id' => $request->filled('category_id') ? $request->category_id : null,
            'publisher_id' => $request->filled('publisher_id') ? $request->publisher_id : null,
            'pages' => $request->filled('pages') ? $request->pages : null,
            'status' => 'pending',
            'allow_download' => $request->boolean('allow_download', true),
        ];

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('proposed_file')) {
            $data['proposed_file'] = $request->file('proposed_file')->store('deposits', 'public');
        }

        // Assigne le responsable le moins chargé
        $manager = \App\Models\User::where('role', 'responsable_demande')
            ->where('status', 'active')
            ->withCount(['assignedDepositRequests' => function ($q) {
                $q->whereIn('status', ['pending', 'second_review']);
            }])
            ->orderBy('assigned_deposit_requests_count')
            ->first();

        if ($manager) {
            $data['assigned_manager_id'] = $manager->id;
        }

        $depositRequest = DepositRequest::create($data);

        // Notifie le responsable de la nouvelle assignation
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

    // Détail d'une demande avec ses relations (utilise Policy pour l'autorisation)
    public function show(DepositRequest $depositRequest)
    {
        $this->authorize('view', $depositRequest);

        return new DepositRequestResource($depositRequest->load([
            'applicant', 'assignedManager', 'reviews.reviewer'
        ]));
    }

    // Modifie le titre ou la description (utilise Policy)
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

    // Supprime une demande (utilise Policy)
    public function destroy(DepositRequest $depositRequest)
    {
        $this->authorize('delete', $depositRequest);

        if ($depositRequest->cover_image) {
            Storage::disk('public')->delete($depositRequest->cover_image);
        }

        $depositRequest->delete();

        return response()->json(null, 204);
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow de validation
    |--------------------------------------------------------------------------
    */

    // Le responsable valide la demande (pending ou second_review)
    public function approveByManager(Request $request, DepositRequest $depositRequest)
    {
        $request->validate(['justification' => 'nullable|string']);

        if (!in_array($depositRequest->status, ['pending', 'second_review'])) {
            return response()->json(['message' => 'Cette demande ne peut plus être modifiée.'], 422);
        }

        DB::transaction(function () use ($request, $depositRequest) {
            $wasSecondReview = $depositRequest->status === 'second_review';

            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'responsable_demande',
                'decision' => 'approved',
                'justification' => $request->justification,
            ]);

            $depositRequest->update(['status' => 'approved_by_manager']);

            // Notifie tous les admins pour publication
            $admins = \App\Models\User::where('role', 'admin')->where('status', 'active')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Demande validée par responsable' . ($wasSecondReview ? ' (second avis)' : ''),
                    'message' => "La demande \"{$depositRequest->title}\" a été validée par le responsable. En attente de publication.",
                    'type' => 'validation',
                ]);
            }
        });

        return response()->json($depositRequest->load('reviews'));
    }

    // Le responsable refuse la demande (justification obligatoire)
    public function rejectByManager(Request $request, DepositRequest $depositRequest)
    {
        $request->validate(['justification' => 'required|string|min:10']);

        if (!in_array($depositRequest->status, ['pending', 'second_review'])) {
            return response()->json(['message' => 'Cette demande ne peut plus être modifiée.'], 422);
        }

        DB::transaction(function () use ($request, $depositRequest) {
            $wasSecondReview = $depositRequest->status === 'second_review';

            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'responsable_demande',
                'decision' => 'rejected',
                'justification' => $request->justification,
            ]);

            $depositRequest->update(['status' => 'rejected_by_manager']);

            // Notifie le demandeur du refus
            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Votre demande a été refusée',
                'message' => "Votre demande \"{$depositRequest->title}\" a été refusée par le responsable.",
                'type' => 'validation',
            ]);

            // Notifie les admins du refus
            $admins = \App\Models\User::where('role', 'admin')->where('status', 'active')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Demande refusée par responsable' . ($wasSecondReview ? ' (second avis)' : ''),
                    'message' => "La demande \"{$depositRequest->title}\" a été refusée. Justification : " . substr($request->justification, 0, 100) . '...',
                    'type' => 'validation',
                ]);
            }
        });

        return response()->json($depositRequest->load('reviews'));
    }

    // Admin : publie la demande validée en créant la référence associée
    public function publish(Request $request, DepositRequest $depositRequest)
    {
        $this->authorize('adminAction', $depositRequest);

        if ($depositRequest->status !== 'approved_by_manager') {
            return response()->json(['message' => 'La demande doit être validée par un responsable avant publication.'], 422);
        }

        $forceDownload = $request->boolean('force_allow_download', false);

        DB::transaction(function () use ($depositRequest, $forceDownload) {
            $referenceData = $this->buildReferenceData($depositRequest);
            if ($forceDownload) {
                $referenceData['allow_download'] = true;
            }
            $reference = Reference::create($referenceData);

            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => auth()->id(),
                'reviewer_role' => 'admin',
                'decision' => 'approved',
                'justification' => 'Publication automatique après validation du responsable.',
            ]);

            $depositRequest->update(['status' => 'published']);

            // Notifie le demandeur que sa référence est publiée
            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Votre référence a été publiée',
                'message' => "Votre demande \"{$depositRequest->title}\" a été publiée avec succès.",
                'type' => 'publication',
            ]);
        });

        // Notification si l'admin force le téléchargement malgré le refus du demandeur
        if ($forceDownload && !$depositRequest->allow_download) {
            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Téléchargement autorisé',
                'message' => "L'administrateur a autorisé le téléchargement de votre publication \"{$depositRequest->title}\" malgré votre refus.",
                'type' => 'information',
            ]);
        }

        Cache::forget('references.featured');
        Cache::forget('stats');

        return response()->json($depositRequest->load('reviews'));
    }

    // Admin : rejette définitivement la demande
    public function rejectByAdmin(Request $request, DepositRequest $depositRequest)
    {
        $this->authorize('adminAction', $depositRequest);

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

            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Votre demande a été rejetée',
                'message' => "Votre demande \"{$depositRequest->title}\" a été rejetée. Motif : " . substr($request->justification, 0, 100),
                'type' => 'validation',
            ]);
        });

        return response()->json($depositRequest->load('reviews'));
    }

    // Admin : passe outre le refus du responsable et publie directement
    public function overrideReject(Request $request, DepositRequest $depositRequest)
    {
        $this->authorize('adminAction', $depositRequest);

        if ($depositRequest->status !== 'rejected_by_manager') {
            return response()->json(['message' => 'Cette demande n\'a pas été refusée par un responsable.'], 422);
        }

        $request->validate(['justification' => 'required|string|min:10']);

        $forceDownload = $request->boolean('force_allow_download', false);

        DB::transaction(function () use ($request, $depositRequest, $forceDownload) {
            DepositRequestReview::create([
                'deposit_request_id' => $depositRequest->id,
                'reviewer_id' => $request->user()->id,
                'reviewer_role' => 'admin',
                'decision' => 'override',
                'justification' => $request->justification,
            ]);

            $referenceData = $this->buildReferenceData($depositRequest);
            if ($forceDownload) {
                $referenceData['allow_download'] = true;
            }
            $reference = Reference::create($referenceData);

            $depositRequest->update(['status' => 'published']);

            // Notifie le demandeur et le responsable de l'override
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

        if ($forceDownload && !$depositRequest->allow_download) {
            Notification::create([
                'user_id' => $depositRequest->applicant_id,
                'title' => 'Téléchargement autorisé',
                'message' => "L'administrateur a autorisé le téléchargement de votre publication \"{$depositRequest->title}\" malgré votre refus.",
                'type' => 'information',
            ]);
        }

        Cache::forget('references.featured');
        Cache::forget('stats');

        return response()->json($depositRequest->load('reviews'));
    }

    // Admin : demande un second avis à un autre responsable
    public function requestSecondReview(Request $request, DepositRequest $depositRequest)
    {
        $this->authorize('adminAction', $depositRequest);

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

            Notification::create([
                'user_id' => $request->new_manager_id,
                'title' => 'Second avis demandé',
                'message' => "Vous êtes sollicité pour un second avis sur \"{$depositRequest->title}\".",
                'type' => 'validation',
            ]);
        });

        return response()->json($depositRequest->load('reviews'));
    }

    // Admin : réassigne la demande à un autre responsable
    public function reassignManager(Request $request, DepositRequest $depositRequest)
    {
        $this->authorize('adminAction', $depositRequest);

        $request->validate([
            'new_manager_id' => 'required|exists:users,id',
        ]);

        $newManager = \App\Models\User::find($request->new_manager_id);
        if (!$newManager || $newManager->role !== 'responsable_demande') {
            return response()->json(['message' => 'L\'utilisateur sélectionné n\'est pas un responsable demande.'], 422);
        }

        $depositRequest->update(['assigned_manager_id' => $request->new_manager_id]);

        Notification::create([
            'user_id' => $request->new_manager_id,
            'title' => 'Nouvelle demande assignée',
            'message' => "La demande \"{$depositRequest->title}\" vous a été réassignée.",
            'type' => 'validation',
        ]);

        return response()->json($depositRequest->load('assignedManager', 'reviews'));
    }

    // Télécharge le fichier proposé d'une demande de dépôt (avec contrôle d'accès)
    public function downloadFile(Request $request, DepositRequest $depositRequest)
    {
        $this->authorize('view', $depositRequest);

        if (!$depositRequest->proposed_file || !Storage::disk('public')->exists($depositRequest->proposed_file)) {
            return response()->json(['message' => 'Fichier non disponible.'], 404);
        }

        return Storage::disk('public')->download($depositRequest->proposed_file);
    }

    private function buildReferenceData(DepositRequest $depositRequest): array
    {
        return [
            'title' => $depositRequest->title,
            'subtitle' => $depositRequest->subtitle,
            'abstract' => $depositRequest->abstract ?? $depositRequest->description,
            'isbn' => $depositRequest->isbn,
            'publication_year' => $depositRequest->publication_year,
            'language' => $depositRequest->language,
            'document_type_id' => $depositRequest->document_type_id,
            'category_id' => $depositRequest->category_id,
            'publisher_id' => $depositRequest->publisher_id,
            'pages' => $depositRequest->pages,
            'cover_image' => $depositRequest->cover_image,
            'file_path' => $depositRequest->proposed_file,
            'uploaded_by' => $depositRequest->applicant_id,
            'status' => 'published',
            'allow_download' => $depositRequest->allow_download,
            'file_size' => $depositRequest->proposed_file && Storage::disk('public')->exists($depositRequest->proposed_file)
                ? Storage::disk('public')->size($depositRequest->proposed_file)
                : null,
        ];
    }
}