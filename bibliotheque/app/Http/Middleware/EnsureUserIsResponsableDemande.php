<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsResponsableDemande
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->user()?->role, ['admin', 'responsable_demande'])) {
            return response()->json(['message' => 'Accès réservé aux responsables des demandes.'], 403);
        }

        return $next($request);
    }
}
