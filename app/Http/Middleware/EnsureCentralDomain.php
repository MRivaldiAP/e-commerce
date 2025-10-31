<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnsureCentralDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $centralDomains = config('tenancy.central_domains', []);
        $host = $request->getHost();

        if (! in_array($host, $centralDomains, true)) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}
