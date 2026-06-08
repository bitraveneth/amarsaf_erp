<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $locale = $request->input('locale', 'en');

        if (! in_array($locale, SetLocale::SUPPORTED, true)) {
            $locale = 'en';
        }

        session(['locale' => $locale]);

        $cookie = cookie('erp_locale', $locale, 60 * 24 * 365);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'locale' => $locale,
            ])->withCookie($cookie);
        }

        return redirect()->back()->withCookie($cookie);
    }
}
