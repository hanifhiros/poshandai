<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $gas)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole($gas)) {
            abort(403, 'Unauthorized - You do not have the required role.');
        }

        return $next($request);
    }
}
