<?php

namespace App\Helpers;

class UiLabels
{
    public static function get(string $key, array $replace = []): string
    {
        return __("ui.{$key}", $replace);
    }

    public static function term(string $key, array $replace = []): string
    {
        return __("ui.terms.{$key}", $replace);
    }

    public static function abbr(string $key): string
    {
        return __("ui.abbr.{$key}");
    }

    public static function dashboard(string $key): string
    {
        return __("ui.dashboards.{$key}");
    }

    public static function action(string $key, array $replace = []): string
    {
        return __("ui.actions.{$key}", $replace);
    }

    /** Humanise DB status slugs for display. */
    public static function status(?string $slug): string
    {
        if ($slug === null || $slug === '') {
            return '—';
        }

        return str($slug)->replace('_', ' ')->title()->toString();
    }
}
