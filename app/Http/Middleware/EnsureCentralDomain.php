<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedDomains = (array) config('tenancy.central_domains', []);

        if (! in_array($request->getHost(), $allowedDomains, true)) {
            abort(403, 'This area is only accessible from the central domain.');
        }

        return $next($request);
    }
}
