<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    /**
     * Routes that should be tracked.
     *
     * @var list<string>
     */
    protected array $trackedRoutes = [
        'home',
        'download',
        'pricing',
        'privacy',
        'terms',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            $this->recordVisit($request);
        }

        return $response;
    }

    /**
     * Determine if the request should be tracked.
     */
    protected function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if ($routeName === null) {
            return false;
        }

        return in_array($routeName, $this->trackedRoutes, true);
    }

    /**
     * Record the page visit.
     */
    protected function recordVisit(Request $request): void
    {
        $userAgent = $request->userAgent() ?? '';

        PageVisit::create([
            'path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr($userAgent, 0, 512),
            'referrer' => $this->sanitizeReferrer($request->header('referer')),
            'device_type' => $this->detectDeviceType($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Sanitize and truncate referrer URL.
     */
    protected function sanitizeReferrer(?string $referrer): ?string
    {
        if ($referrer === null || $referrer === '') {
            return null;
        }

        return mb_substr($referrer, 0, 2048);
    }

    /**
     * Detect device type from user agent.
     */
    protected function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/(mobile|iphone|ipod|android|blackberry|opera mini|iemobile)/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Detect browser from user agent.
     */
    protected function detectBrowser(string $userAgent): ?string
    {
        $browsers = [
            'Edge' => '/edg/i',
            'Opera' => '/opera|opr/i',
            'Chrome' => '/chrome|chromium|crios/i',
            'Safari' => '/safari/i',
            'Firefox' => '/firefox|fxios/i',
            'IE' => '/msie|trident/i',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }

        return null;
    }

    /**
     * Detect platform from user agent.
     */
    protected function detectPlatform(string $userAgent): ?string
    {
        $platforms = [
            'Windows' => '/windows/i',
            'macOS' => '/macintosh|mac os x/i',
            'Linux' => '/linux/i',
            'iOS' => '/iphone|ipad|ipod/i',
            'Android' => '/android/i',
        ];

        foreach ($platforms as $platform => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $platform;
            }
        }

        return null;
    }
}
