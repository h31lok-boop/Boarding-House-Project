<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperDuperAdmin
{
    /**
     * Ensure the authenticated user has superduperadmin access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $isLegacySuper = strtolower((string) $user->role) === 'superduperadmin';
        $isSpatieSuper = method_exists($user, 'hasRole') && $user->hasRole('superduperadmin');

        if (! $isLegacySuper && ! $isSpatieSuper) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
