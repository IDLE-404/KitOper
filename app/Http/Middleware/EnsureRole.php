<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Ensure the authenticated user has one of the allowed roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $allowed = array_filter(array_map('trim', $roles));
        if ($allowed && !in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
