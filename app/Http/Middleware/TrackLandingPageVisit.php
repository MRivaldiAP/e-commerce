<?php

namespace App\Http\Middleware;

use App\Models\LandingPageVisit;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLandingPageVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->isMethod('get')) {
            $this->recordVisit($request, $response);
        }

        return $response;
    }

    protected function recordVisit(Request $request, Response $response): void
    {
        $page = '/' . ltrim($request->path(), '/');
        if ($page === '//') {
            $page = '/';
        }

        $today = Carbon::today();
        $cookieName = 'landing_visit_' . md5($page . '_' . $today->toDateString());
        $hasUniqueCookie = $request->cookies->has($cookieName);
        $isUniqueVisit = ! $hasUniqueCookie;

        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        $refererHost = $referer ? parse_url($referer, PHP_URL_HOST) : null;
        $isPrimaryVisit = empty($refererHost) || $refererHost !== $host;

        $visit = LandingPageVisit::firstOrCreate([
            'page' => $page,
            'visit_date' => $today->toDateString(),
        ]);

        $visit->increment('total_visits');

        if ($isUniqueVisit) {
            $visit->increment('unique_visits');

            $minutesRemaining = $today->copy()->endOfDay()->diffInMinutes(Carbon::now());
            $response->headers->setCookie(cookie()->make($cookieName, true, $minutesRemaining > 0 ? $minutesRemaining : 1));
        }

        if ($isPrimaryVisit) {
            $visit->increment('primary_visits');
        } else {
            $visit->increment('secondary_visits');
        }
    }
}
