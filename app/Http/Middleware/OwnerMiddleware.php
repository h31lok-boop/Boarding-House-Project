<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isStrictOwner()) {
            // Super-admins are sent to their own workspace; everyone else is denied.
            if ($user->isSuperAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            abort(403, 'Access denied. This page is only for property owners.');
        }

        return $next($request);
    }
}
