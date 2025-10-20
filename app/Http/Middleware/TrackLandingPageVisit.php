<?php

namespace App\Http\Middleware;

use App\Models\LandingPageVisit;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class TrackLandingPageVisit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        $page = $this->resolvePageIdentifier($request);
        $today = Carbon::today();

        $sessionKey = sprintf('landing_page_unique_visits.%s.%s', $today->toDateString(), $page);
        $session = $request->session();
        $isUniqueVisit = ! $session->get($sessionKey, false);

        if ($isUniqueVisit) {
            $session->put($sessionKey, true);
        }

        $isPrimaryVisit = $this->isPrimaryVisit($request);

        $visit = LandingPageVisit::query()->firstOrCreate([
            'page' => $page,
            'date' => $today->toDateString(),
        ]);

        $visit->increment('total_visits');

        if ($isUniqueVisit) {
            $visit->increment('unique_visits');
        }

        if ($isPrimaryVisit) {
            $visit->increment('primary_visits');
        } else {
            $visit->increment('secondary_visits');
        }

        return $response;
    }

    private function shouldTrack(Request $request, $response): bool
    {
        if (! $request->isMethod('get')) {
            return false;
        }

        if (! $request->route()) {
            return false;
        }

        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 400) {
            return false;
        }

        return true;
    }

    private function resolvePageIdentifier(Request $request): string
    {
        $routeName = $request->route()->getName();

        if (! empty($routeName)) {
            return $routeName;
        }

        $path = trim($request->path(), '/');

        return $path === '' ? 'home' : $path;
    }

    private function isPrimaryVisit(Request $request): bool
    {
        $referer = $request->headers->get('referer');

        if (! $referer) {
            return true;
        }

        $refererHost = parse_url($referer, PHP_URL_HOST);

        if (! $refererHost) {
            return true;
        }

        return $refererHost !== $request->getHost();
    }
}
