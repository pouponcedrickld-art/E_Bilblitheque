# Plan de correction — Bugs test manuel

## Étape 1 — Déplacer les routes stats hors du groupe admin

**Fichier :** `bibliotheque/routes/api.php`

**Supprimer** les lignes 122-123 et 126-127 du groupe `admin` (bloc lignes 89-140) :
```php
Route::get('/downloads/stats', [DownloadController::class, 'stats']);
Route::get('/views/stats', [ViewController::class, 'stats']);
```

**Ajouter** ces 2 lignes **avant** la ligne 73 (avant le middleware `responsable.demande`) :
```php
    // Statistiques (admin et RH)
    Route::get('/downloads/stats', [DownloadController::class, 'stats']);
    Route::get('/views/stats', [ViewController::class, 'stats']);

    // --- Admin + Responsable Demande ---
    Route::middleware('responsable.demande')->group(function () {
```

## Étape 2 — Ajouter le garde-fou rôle dans DownloadController@stats

**Fichier :** `bibliotheque/app/Http/Controllers/Api/DownloadController.php`

Ajouter en début de méthode `stats()` (après `public function stats(Request $request)`):
```php
if (!in_array($request->user()->role, ['admin', 'responsable_rh'])) {
    return response()->json(['message' => 'Non autorisé.'], 403);
}
```

## Étape 3 — Dashboard.vue : appels conditionnels + clés API

**Fichier :** `bibliotheque-front/src/Pages/User/Dashboard.vue`

Remplacer les lignes 42-49 (bloc `fetchStats`) par :
```js
    const [refs, cats, authors, users, downloads, views] = await Promise.all([
      http.get('/references').catch(() => ({ data: { data: [] } })),
      http.get('/categories').catch(() => ({ data: { data: [] } })),
      http.get('/authors').catch(() => ({ data: { data: [] } })),
      (authStore.isAdmin || authStore.isResponsableRH) ? http.get('/users').catch(() => ({ data: { data: [] } })) : Promise.resolve({ data: { data: [] } }),
      (authStore.isAdmin || authStore.isResponsableRH) ? http.get('/downloads/stats').catch(() => ({ data: {} })) : Promise.resolve({ data: {} }),
      authStore.isAdmin ? http.get('/views/stats').catch(() => ({ data: {} })) : Promise.resolve({ data: {} }),
    ])
```

Remplacer les lignes 57-58 :
```js
      downloads: downloads.data?.total_downloads ?? 0,
      views: views.data?.total_views ?? 0,
```

## Étape 4 — Élargir le filtre rôles dans Review.vue

**Fichier :** `bibliotheque-front/src/Pages/Admin/DepositRequests/Review.vue`

Remplacer ligne 50-51 (le `.filter(...)`) par :
```js
    users.value = (usersRes.data?.data ?? usersRes.data ?? []).filter(
      (u: any) => ['admin', 'responsable_rh', 'responsable_demande'].includes(u.role)
    )
```

## Vérification

Après les modifications :
1. Lancer `npm run dev` côté frontend et `php artisan serve` côté backend
2. Vérifier que la console n'affiche plus de 403 sur `/api/downloads/stats` ni `/api/views/stats` pour les rôles non-admin
3. Vérifier que les valeurs `Téléchargements` et `Consultations` s'affichent correctement dans les cartes admin/RH
4. Vérifier que le Select "Second avis" propose bien les admins et RH en plus des responsables demande
