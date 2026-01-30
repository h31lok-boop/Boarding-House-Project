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

        // Prefer Spatie roles when available, fall back to legacy column.
        $isAdminRole = method_exists($user, 'hasRole') && $user->hasRole('admin');
        $isLegacyAdmin = $user->role === 'admin' || $user->role === 'owner';

        if (! $isAdminRole && ! $isLegacyAdmin) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
