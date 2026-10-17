<?php

use App\Exceptions\PlanLimitExceeded;
use App\Http\Middleware\ResolveApiTeam;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'api.team' => ResolveApiTeam::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/billing',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PlanLimitExceeded $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->with('error', $exception->getMessage());
        });
    })->create();
