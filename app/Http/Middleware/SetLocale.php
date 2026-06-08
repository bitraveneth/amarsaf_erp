<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var array<int, string> */
    public const SUPPORTED = ['en', 'bn'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! is_string($locale) || ! in_array($locale, self::SUPPORTED, true)) {
            $cookie = $request->cookie('erp_locale');
            $locale = is_string($cookie) && in_array($cookie, self::SUPPORTED, true)
                ? $cookie
                : config('app.locale', 'en');
        }

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
