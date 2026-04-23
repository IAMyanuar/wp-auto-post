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
        // Percaya pada semua proxy (seperti Ngrok) agar URL dan redirect terbentuk sebagai HTTPS
        $middleware->trustProxies(at: '*');

        // Webhook dari n8n tidak menggunakan CSRF token (dipanggil server-to-server)
        $middleware->validateCsrfTokens(except: [
            'webhook/n8n/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
