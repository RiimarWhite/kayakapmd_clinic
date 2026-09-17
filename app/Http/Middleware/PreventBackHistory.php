<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * Detailed Comment: Appends strict cache control headers to prevent web browsers from
     * storing authenticated dashboard pages in back-forward cache (bfcache).
     * This ensures that when a user logs out and clicks the browser 'Back' button,
     * the browser is forced to query the server, where the auth middleware will reject the
     * request and redirect to the login page across XAMPP, Docker Apache, and Artisan serve.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Detailed Comment: Set HTTP headers to completely disable client-side caching for HTML responses
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
