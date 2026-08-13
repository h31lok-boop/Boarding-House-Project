<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ForceUtf8ResponseHeaders;
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ForceUtf8ResponseHeaders::class);

        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'owner' => OwnerMiddleware::class,
            'user' => UserMiddleware::class,
        ]);

        $middleware->trustHosts(at: ['^(.*)\.ngrok-free\.dev$', '^localhost$', '^final-project\.test$']);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
