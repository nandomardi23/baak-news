<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReleaseSessionLock
{
    /**
     * Handle an incoming request.
     * Releases session lock immediately for long-running operations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Save and close session immediately to release lock
        if (session()->isStarted()) {
            session()->save();
            session()->driver()->getHandler()->close();
        }
        
        return $next($request);
    }
}
