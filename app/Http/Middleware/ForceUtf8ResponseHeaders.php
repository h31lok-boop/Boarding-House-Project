<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8ResponseHeaders
{
    /**
     * Ensure JSON responses explicitly advertise UTF-8.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');
        $normalizedContentType = strtolower($contentType);

        if ($response instanceof JsonResponse || str_contains($normalizedContentType, 'application/json')) {
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');

            return $response;
        }

        if (
            $contentType !== ''
            && str_contains($normalizedContentType, 'text/html')
            && ! str_contains($normalizedContentType, 'charset=')
        ) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }
}
