<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keep local dev on one host so session cookies are not split between
 * localhost and 127.0.0.1 (a common cause of 419 on login).
 */
class NormalizeLocalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        $preferredHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! is_string($preferredHost) || $preferredHost === '') {
            return $next($request);
        }

        $currentHost = $request->getHost();

        $localHosts = ['localhost', '127.0.0.1', '[::1]', 'saf_erp.test'];

        $currentHostLower = strtolower($currentHost);
        $preferredHostLower = strtolower($preferredHost);

        if ($currentHostLower === $preferredHostLower) {
            return $next($request);
        }

        $isCurrentLocal = in_array($currentHostLower, $localHosts, true)
            || str_ends_with($currentHostLower, '.test');
        $isPreferredLocal = in_array($preferredHostLower, $localHosts, true)
            || str_ends_with($preferredHostLower, '.test');

        if (! $isCurrentLocal || ! $isPreferredLocal) {
            return $next($request);
        }

        $target = $request->getScheme().'://'.$preferredHost;

        $preferredPort = parse_url((string) config('app.url'), PHP_URL_PORT);
        if ($preferredPort && ! in_array((int) $preferredPort, [80, 443], true)) {
            $target .= ':'.$preferredPort;
        }

        $target .= $request->getRequestUri();

        return redirect()->to($target, 302);
    }
}
