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

        $user->loadMissing('ownerProfile');
        $status = strtolower((string) ($user->status ?: ($user->account_status ?? 'pending')));
        $profile = $user->ownerProfile;
        $hasPermit = filled($profile?->proof_of_ownership) || filled($profile?->valid_id_file);
        $isVerified = strtolower((string) $profile?->verification_status) === 'verified';

        if ($status !== 'active' || ! $user->is_active || ! $hasPermit || ! $isVerified) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This owner account cannot be used until an administrator verifies its business permit.',
            ]);
        }

        return $next($request);
    }
}
