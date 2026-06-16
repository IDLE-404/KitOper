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
        // За nginx-реверс-прокси на той же машине — доверяем заголовкам X-Forwarded-*,
        // иначе Laravel не увидит https и будет генерировать http-ссылки.
        $middleware->trustProxies(at: '127.0.0.1');

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'audit' => \App\Http\Middleware\AuditLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
