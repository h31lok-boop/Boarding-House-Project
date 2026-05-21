<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Ensure the current user is an admin/owner.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Owner accounts are presented as Admin accounts in the application UI.
        $isAdminRole = method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('owner'));
        $isSuperRole = method_exists($user, 'hasRole') && $user->hasRole('superduperadmin');
        $isLegacyAdmin = in_array(strtolower((string) $user->role), ['admin', 'owner'], true);
        $isLegacySuper = strtolower((string) $user->role) === 'superduperadmin';

        if (! $isAdminRole && ! $isLegacyAdmin && ! $isSuperRole && ! $isLegacySuper) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
