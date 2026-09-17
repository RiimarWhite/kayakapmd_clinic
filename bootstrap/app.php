<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Detailed Comment: Register role and prevent-back-history middleware aliases
        $middleware->alias([
            'role' => App\Http\Middleware\RoleMiddleware::class,
            'prevent-back-history' => App\Http\Middleware\PreventBackHistory::class,
            'request-logging' => App\Http\Middleware\RequestLoggingMiddleware::class,
        ]);

        // Detailed Comment: Enforce anti-cache headers across all web routes so logged-out users cannot view cached pages via the Back button
        $middleware->appendToGroup('web', App\Http\Middleware\PreventBackHistory::class);

        // Detailed Comment: Append RequestLoggingMiddleware to web and api groups for comprehensive HTTP diagnostic logging
        $middleware->appendToGroup('web', App\Http\Middleware\RequestLoggingMiddleware::class);
        $middleware->appendToGroup('api', App\Http\Middleware\RequestLoggingMiddleware::class);

        // Detailed Comment: Explicitly redirect unauthenticated guests to the named 'login' route across all environments
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Detailed Comment: Capture and log all unhandled exceptions with request context to storage/logs/laravel.log
        $exceptions->reportable(function (\Throwable $e) {
            // Detailed Comment: Guard against recursive crash if Monolog itself threw an unopenable stream exception
            if ($e instanceof \UnexpectedValueException && str_contains($e->getMessage(), 'storage/logs')) {
                return false;
            }

            $request = request();
            \Illuminate\Support\Facades\Log::error('Unhandled exception captured', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'path' => $request ? $request->path() : 'cli',
                'method' => $request ? $request->method() : 'cli',
                'ip' => $request ? $request->ip() : null,
            ]);
        });
    })->create();
