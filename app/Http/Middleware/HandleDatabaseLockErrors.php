<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class HandleDatabaseLockErrors
{
    public function handle(Request $request, Closure $next)
    {
        // Retry logic for database lock errors during auth resolution
        $maxAttempts = 3;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            try {
                // Trigger auth check which may lock DB
                if (auth()->check()) {
                    // Success — continue
                }
                return $next($request);
            } catch (QueryException $e) {
                $lastException = $e;
                // Check if error is "database disk image is malformed" or "database is locked"
                if (strpos($e->getMessage(), 'malformed') !== false || 
                    strpos($e->getMessage(), 'locked') !== false || 
                    strpos($e->getMessage(), 'I/O error') !== false) {
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        usleep(100000 * $attempt); // Exponential backoff: 100ms, 200ms, 300ms
                        continue;
                    }
                }
                throw $e;
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        return $next($request);
    }
}
