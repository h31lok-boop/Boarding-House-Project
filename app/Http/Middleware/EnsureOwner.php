<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $hasRole = $user && method_exists($user, 'hasRole') && ($user->hasRole('owner') || $user->hasRole('admin'));
        $legacy = $user && in_array(strtolower((string) $user->role), ['owner', 'admin'], true);

        abort_unless($hasRole || $legacy, 403);

        return $next($request);
    }
}
