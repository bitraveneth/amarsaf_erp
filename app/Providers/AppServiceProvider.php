<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app-header', 'layouts.partials.admin-header'], function ($view) {
            if (!auth()->check()) {
                return;
            }

            try {
                $notificationsTableReady = Schema::hasTable('notifications');
            } catch (Throwable $e) {
                $notificationsTableReady = false;
            }

            if (!$notificationsTableReady) {
                $view->with('headerAlerts', collect());
                $view->with('headerAlertCount', 0);
                return;
            }

            $user = auth()->user();

            $headerUnread = $user->unreadNotifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($notification) {
                    $data = $notification->data;
                    $fallbackSource = class_basename($notification->type) === 'SystemAlertNotification'
                        ? ($data['source'] ?? 'System')
                        : 'General';

                    return [
                        'id' => $notification->id,
                        'message' => $data['message'] ?? '',
                        'variant' => $data['type'] ?? 'info',
                        'source' => $data['source'] ?? $fallbackSource,
                        'created_at' => $notification->created_at,
                        'dedupe_key' => $data['dedupe_key'] ?? null,
                    ];
                });

            $headerUnreadCount = $user->unreadNotifications()->count();

            // Use dedicated header-scoped variables to avoid conflicts with page-local `$alerts`.
            $view->with('headerAlerts', $headerUnread);
            $view->with('headerAlertCount', $headerUnreadCount);
        });
    }
}
