@auth
    @php
        $alertCollection = isset($headerAlerts)
            ? collect($headerAlerts)
                ->values()
                ->map(function ($alert) {
                    return is_array($alert)
                        ? $alert
                        : ['message' => (string) $alert, 'variant' => 'error'];
                })
            : collect();
        $alertCount = isset($headerAlertCount) ? (int) $headerAlertCount : $alertCollection->count();
        $trayAlerts = $alertCollection->take(10);
    @endphp

    <div class="header-alert js-header-alert-root relative" data-fetch-url="{{ route('admin.notifications.header-data') }}">
        <button
            type="button"
            data-tour="header-alerts"
            class="header-alert-toggle header-icon-btn"
            title="{{ __('app.notifications.title') }}"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 3.5C9.51472 3.5 7.5 5.51472 7.5 8V10.2344C7.5 11.0897 7.21486 11.9207 6.68945 12.5957L5.73047 13.8291C5.20006 14.5111 5.68643 15.5 6.55078 15.5H17.4492C18.3136 15.5 18.7999 14.5111 18.2695 13.8291L17.3105 12.5957C16.7851 11.9207 16.5 11.0897 16.5 10.2344V8C16.5 5.51472 14.4853 3.5 12 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9.5 17.5C9.80616 18.3734 10.6383 19 11.625 19H12.375C13.3617 19 14.1938 18.3734 14.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>

            <span class="js-header-alert-count absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-error-500 px-1 text-[10px] font-semibold text-white {{ $alertCount ? '' : 'hidden' }}">
                {{ $alertCount }}
            </span>
        </button>

        <div class="header-alert-menu fixed inset-x-4 top-[4.75rem] z-40 flex max-h-[70vh] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white text-sm shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 xl:absolute xl:inset-x-auto xl:right-0 xl:top-full xl:mt-2 xl:w-[27rem] xl:max-h-none">
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ __('app.notifications.title') }}
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="js-header-alert-active-count">{{ $alertCount }}</span> {{ __('app.notifications.active') }}
                    </p>
                </div>
                <button
                    type="button"
                    class="header-alert-menu-close flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                    aria-label="Close alerts"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 6L14 14M14 6L6 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            @if ($alertCount)
                <ul class="js-header-alert-list max-h-[430px] space-y-2 overflow-y-auto p-4 text-[13px] text-gray-700 dark:text-gray-300">
                    @foreach ($trayAlerts as $alert)
                        @php
                            $message = $alert['message'] ?? '';
                            $variant = $alert['variant'] ?? 'error';
                            $alertStyles = [
                                'error' => [
                                    'ring' => 'border-error-300/70 bg-error-50/70 dark:border-error-700/60 dark:bg-error-500/10',
                                    'dot' => 'bg-error-500',
                                    'label' => 'Error',
                                    'labelClass' => 'text-error-700 dark:text-error-300',
                                ],
                                'warning' => [
                                    'ring' => 'border-warning-300/70 bg-warning-50/70 dark:border-warning-700/60 dark:bg-warning-500/10',
                                    'dot' => 'bg-warning-500',
                                    'label' => 'Warning',
                                    'labelClass' => 'text-warning-700 dark:text-warning-300',
                                ],
                                'success' => [
                                    'ring' => 'border-success-300/70 bg-success-50/70 dark:border-success-700/60 dark:bg-success-500/10',
                                    'dot' => 'bg-success-500',
                                    'label' => 'Success',
                                    'labelClass' => 'text-success-700 dark:text-success-300',
                                ],
                                'info' => [
                                    'ring' => 'border-brand-300/70 bg-brand-50/70 dark:border-brand-700/60 dark:bg-brand-500/10',
                                    'dot' => 'bg-brand-500',
                                    'label' => 'Info',
                                    'labelClass' => 'text-brand-700 dark:text-brand-300',
                                ],
                            ][$variant] ?? [
                                'ring' => 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50',
                                'dot' => 'bg-gray-400',
                                'label' => 'Notice',
                                'labelClass' => 'text-gray-600 dark:text-gray-300',
                            ];
                            $sourceLabel = $alert['source'] ?? $alertStyles['label'];
                            $notificationId = $alert['id'] ?? null;
                            $openUrl = $alert['open_url'] ?? ($notificationId && Route::has('admin.notifications.open') ? route('admin.notifications.open', $notificationId) : '#');
                            $timeLabel = $alert['time_label'] ?? ($alert['created_at'] ? \Carbon\Carbon::parse($alert['created_at'])->diffForHumans() : 'Now');
                        @endphp
                        <li class="js-header-alert-item">
                            <a href="{{ $openUrl }}" class="header-alert-action group block rounded-xl border px-3 py-2.5 transition hover:shadow-sm {{ $alertStyles['ring'] }}">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full {{ $alertStyles['dot'] }}"></span>
                                        <span class="text-[11px] font-semibold uppercase tracking-wide {{ $alertStyles['labelClass'] }}">{{ $sourceLabel }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $timeLabel }}</span>
                                        <svg class="h-3.5 w-3.5 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-brand-600 dark:group-hover:text-brand-400" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="leading-5 text-gray-800 dark:text-gray-100">{{ $message }}</p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="js-header-alert-empty p-4">
                    <p class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-[13px] text-gray-500 dark:border-gray-700 dark:bg-white/5 dark:text-gray-400">
                        {{ __('app.notifications.none') }}
                    </p>
                </div>
            @endif

            <div class="border-t border-gray-100 p-3 dark:border-gray-800">
                <a href="{{ route('admin.notifications.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('app.notifications.view_all') }}
                </a>
            </div>
        </div>
    </div>
@endauth
