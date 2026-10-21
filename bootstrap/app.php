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
        $middleware->alias(['talento-humano' => \App\Http\Middleware\EsTalentoHumano::class]);
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->esTalentoHumano() ? '/talento-humano' : '/postulante');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
