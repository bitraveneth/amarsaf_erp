<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    protected function inExceptArray($request)
    {
        if (
            app()->environment('local')
            && $request->isMethod('POST')
            && ($request->is('/') || $request->is('login'))
        ) {
            return true;
        }

        return parent::inExceptArray($request);
    }
}
