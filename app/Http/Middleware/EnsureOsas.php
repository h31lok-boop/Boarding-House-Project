<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOsas
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $hasRole = $user && (method_exists($user, 'hasRole') && $user->hasRole('osas'));
        $legacy = $user && strtolower($user->role ?? '') === 'osas';
        abort_unless($hasRole || $legacy, 403);
        return $next($request);
    }
}
