<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    /**
     * Paths excluded from high-frequency logging to avoid log pollution.
     */
    protected array $excludedPaths = [
        'up',
        'generate_new_codes',
        'fetch_todays_patients',
        'fetch_consultation_patients',
        'fetch_today_patients',
    ];

    /**
     * Handle an incoming request.
     * Detailed Comment: Structured logging middleware recording HTTP request and response metrics.
     * Captures method, URI, authenticated guard/user, client IP, response status, and duration (ms).
     * Filters out repetitive polling endpoints per project performance and logging guidelines.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // Detailed Comment: Skip high-frequency polling paths to keep log files readable and actionable
        foreach ($this->excludedPaths as $pattern) {
            if ($request->is($pattern) || $request->is('*' . $pattern . '*')) {
                return $response;
            }
        }

        $authenticatedGuard = null;
        $authenticatedUserId = null;

        foreach (array_keys(config('auth.guards')) as $guard) {
            if (Auth::guard($guard)->check()) {
                $authenticatedGuard = $guard;
                $authenticatedUserId = Auth::guard($guard)->id();
                break;
            }
        }

        $context = [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'ip' => $request->ip(),
            'guard' => $authenticatedGuard,
            'user_id' => $authenticatedUserId,
        ];

        // Detailed Comment: Log state-modifying requests at INFO and read-only GETs at DEBUG
        if ($request->isMethodSafe()) {
            Log::debug('HTTP GET processed', $context);
        } else {
            Log::info('HTTP mutation processed', $context);
        }

        return $response;
    }
}
