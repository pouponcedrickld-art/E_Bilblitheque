<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->method(),
                'target_table' => $this->getTargetTable($request),
                'target_id' => (function () use ($request) {
                    $first = collect($request->route()->parameters())->first();
                    return $first instanceof \Illuminate\Database\Eloquent\Model ? $first->getKey() : $first;
                })(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }

    private function getTargetTable(Request $request): ?string
    {
        $path = $request->path();
        
        return match(true) {
            str_contains($path, 'categories') => 'categories',
            str_contains($path, 'authors') => 'authors',
            str_contains($path, 'publishers') => 'publishers',
            str_contains($path, 'references') => 'references',
            str_contains($path, 'users') => 'users',
            str_contains($path, 'suspension-requests') => 'suspension_requests',
            str_contains($path, 'deposit-requests') => 'deposit_requests',
            default => null,
        };
    }
}
