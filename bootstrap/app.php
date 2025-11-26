<?php

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
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\TrackPageVisits::class,
        ]);

        // Excluir callback de Pago Fácil de verificación CSRF
        $middleware->validateCsrfTokens(except: [
            'api/pagofacil/callback',
        ]);

        //
        $middleware->alias([
            'propietario' => \App\Http\Middleware\EnsureUserIsPropietario::class,
            'cliente' => \App\Http\Middleware\EnsureUserIsCliente::class,
            'trabajador' => \App\Http\Middleware\TrabajadorMiddleware::class,
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: function ($request) {
                if ($request->user()->isPropietario()) {
                    return '/admin/dashboard';
                } elseif ($request->user()->isTrabajador()) {
                    return '/trabajador/dashboard';
                }
                return '/dashboard';
            }
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
