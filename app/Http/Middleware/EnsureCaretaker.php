<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCaretaker
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $hasRole = $user && (method_exists($user, 'hasRole') && $user->hasRole('caretaker'));
        $legacy = $user && strtolower($user->role ?? '') === 'caretaker';
        abort_unless($hasRole || $legacy, 403);
        return $next($request);
    }
}
