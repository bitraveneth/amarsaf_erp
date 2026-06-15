<?php

namespace App\Providers;

use App\Helpers\HeaderMenuSearch;
use App\Helpers\SystemSettings;
use App\Services\NotificationActionResolver;
use App\Services\Inventory\ProcurementInboxService;
use App\Services\Sales\FulfillmentInboxService;
use App\Support\Learning\LearningHubRepository;
use App\Support\Learning\LearningRouteMap;
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
        $appName = config('app.name');
        $brandName = SystemSettings::brandName($appName);
        $legalCompanyName = SystemSettings::legalCompanyName($appName);
        $currencyCode = SystemSettings::get('currency_code', config('app.currency', 'BDT'));
        $currencySymbol = SystemSettings::get('currency_symbol', '৳');
        $logoUrl = SystemSettings::logoUrl();
        $brandThemeVariables = SystemSettings::brandThemeVariables();
        $defaultThemeMode = SystemSettings::defaultThemeMode();

        config([
            'app.name' => $appName,
            'app.currency' => $currencyCode,
            'app.currency_symbol' => $currencySymbol,
            'mail.mailers.smtp.host' => SystemSettings::get('smtp_host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => (int) SystemSettings::get('smtp_port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.encryption' => SystemSettings::get('smtp_encryption', config('mail.mailers.smtp.encryption')),
            'mail.mailers.smtp.username' => SystemSettings::get('smtp_username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => SystemSettings::get('smtp_password', config('mail.mailers.smtp.password')),
            'mail.from.address' => SystemSettings::get('mail_from_address', config('mail.from.address')),
            'mail.from.name' => SystemSettings::get('mail_from_name', $appName),
            'services.sms.provider' => SystemSettings::get('sms_provider'),
            'services.sms.base_url' => SystemSettings::get('sms_base_url'),
            'services.sms.api_key' => SystemSettings::get('sms_api_key'),
            'services.sms.api_secret' => SystemSettings::get('sms_api_secret'),
            'services.sms.sender_id' => SystemSettings::get('sms_sender_id'),
        ]);

        View::share('appLogoUrl', $logoUrl);
        View::share('appLogoIsCustom', SystemSettings::hasCustomLogo());
        View::share('brandThemeVariables', $brandThemeVariables);
        View::share('defaultThemeMode', $defaultThemeMode);
        View::share('appBrandName', $brandName);
        View::share('appBrandDisplay', SystemSettings::brandDisplay($brandName));
        View::share('appBrandInitials', SystemSettings::initials($brandName));
        View::share('legalCompanyName', $legalCompanyName);
        View::share('legalCompanyInitials', SystemSettings::initials($legalCompanyName));
        View::share('headerNotice', SystemSettings::headerNotice());

        View::composer(['layouts.app-header', 'layouts.partials.admin-header'], function ($view) {
            if (!auth()->check()) {
                $view->with('menuSearchItems', collect());
                $view->with('headerAlerts', collect());
                $view->with('headerAlertCount', 0);

                return;
            }

            $view->with('menuSearchItems', HeaderMenuSearch::itemsForUser(auth()->user()));

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
                ->map(fn ($notification) => NotificationActionResolver::present($notification));

            $headerUnreadCount = $user->unreadNotifications()->count();

            // Use dedicated header-scoped variables to avoid conflicts with page-local `$alerts`.
            $view->with('headerAlerts', $headerUnread);
            $view->with('headerAlertCount', $headerUnreadCount);
        });

        View::composer(['layouts.app', 'layouts.sidebar', 'layouts.app-header'], function ($view) {
            if (! auth()->check()) {
                $view->with('procurementInbox', []);
                $view->with('fulfillmentInbox', []);
                $view->with('learningModuleSlug', null);
                $view->with('learningModule', null);
                $view->with('learningUi', LearningHubRepository::ui());
                $view->with('learningHubUrl', route('admin.learning-hub', [], false));

                return;
            }

            $view->with('procurementInbox', app(ProcurementInboxService::class)->forUser(auth()->user()));
            $view->with('fulfillmentInbox', app(FulfillmentInboxService::class)->forUser(auth()->user()));

            $slug = LearningRouteMap::slugForRequest();
            $module = $slug ? LearningHubRepository::module($slug) : null;
            $hubUrl = $slug
                ? route('admin.learning-hub', ['module' => $slug])
                : route('admin.learning-hub');

            $view->with('learningModuleSlug', $slug);
            $view->with('learningModule', $module);
            $view->with('learningUi', LearningHubRepository::ui());
            $view->with('learningHubUrl', $hubUrl);
        });
    }
}
