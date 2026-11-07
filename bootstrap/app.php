<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\NoCacheForGuest;
use App\Http\Middleware\SessionTimeout;
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
        $middleware->trustProxies(at: '*');

        // Nama pendek yang dipakai di routes/web.php
        $middleware->alias([
            'role'           => EnsureRole::class,
            'no-cache-guest' => NoCacheForGuest::class,
        ]);

        // Timeout sesi untuk semua route web
        $middleware->appendToGroup('web', SessionTimeout::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();