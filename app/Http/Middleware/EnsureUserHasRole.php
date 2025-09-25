<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! $user->hasAnyRole(...$roles)) {
            abort(403);
        }

        return $next($request);
    }
}
