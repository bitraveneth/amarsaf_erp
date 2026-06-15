@php
    use App\Helpers\MenuHelper;
    use App\Helpers\Permission;
    $__authUser = auth()->user();
    $__menuGroups = collect(MenuHelper::getMenuGroups())
        ->map(function ($group) use ($__authUser) {
            $items = collect($group['items'] ?? [])
                ->map(function ($item) use ($__authUser) {
                    $itemPermission = $item['permission'] ?? null;
                    $canSeeItem = empty($itemPermission) || Permission::can($__authUser, $itemPermission);

                    if (!$canSeeItem) {
                        return null;
                    }

                    if (! MenuHelper::isValidMenuPath($item['path'] ?? null)) {
                        return null;
                    }

                    if (isset($item['subItems']) && is_array($item['subItems'])) {
                        $item['subItems'] = collect($item['subItems'])
                            ->filter(function ($subItem) use ($__authUser) {
                                $permission = $subItem['permission'] ?? null;
                                $hasPermission = empty($permission) || Permission::can($__authUser, $permission);
                                if (!$hasPermission) {
                                    return false;
                                }

                                if (! MenuHelper::isValidMenuPath($subItem['path'] ?? null)) {
                                    return false;
                                }

                                return true;
                            })
                            ->values()
                            ->all();
                    }

                    return $item;
                })
                ->filter()
                ->values()
                ->all();

            return [
                'title' => $group['title'] ?? '',
                'items' => $items,
            ];
        })
        ->filter(fn ($group) => !empty($group['items']))
        ->values();

    $__menuSearchItems = $__menuGroups
        ->flatMap(function ($group) {
            return collect($group['items'])->flatMap(function ($item) use ($group) {
                $items = [];

                if (! empty($item['path']) && $item['path'] !== '#') {
                    $items[] = [
                        'label' => $item['name'],
                        'path' => $item['path'],
                        'group' => $group['title'],
                    ];
                }

                foreach ($item['subItems'] ?? [] as $sub) {
                    if (! empty($sub['path']) && $sub['path'] !== '#') {
                        $items[] = [
                            'label' => $sub['name'],
                            'path' => $sub['path'],
                            'group' => $group['title'],
                        ];
                    }
                }

                return $items;
            });
        })
        ->values();
@endphp

<header
    class="app-header print-hidden"
    x-data="{
        isMobileSearchOpen: false,
        toggleMobileSearch() {
            this.isMobileSearchOpen = !this.isMobileSearchOpen;
        }
    }">
    <div class="app-header__inner">
        <div class="app-header__start">
            <button
                class="header-toggle-btn header-icon-btn hidden xl:inline-flex"
                :class="{ 'is-active': !$store.sidebar.isExpanded }"
                @click="$store.sidebar.toggleExpanded()"
                aria-label="Toggle sidebar">
                <svg x-show="!$store.sidebar.isMobileOpen" width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor" />
                </svg>
            </button>

            <button
                class="header-toggle-btn header-icon-btn inline-flex xl:hidden"
                :class="{ 'is-active': $store.sidebar.isMobileOpen }"
                @click="$store.sidebar.toggleMobileOpen()"
                aria-label="Toggle mobile menu">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor" />
                </svg>
            </button>

            <x-layout.header-breadcrumbs />
        </div>

        <div class="app-header__search">
            <x-layout.header-search :items="$__menuSearchItems" wide class="w-full max-w-xl" />
        </div>

        <div class="app-header__actions">
            <button
                type="button"
                class="header-icon-btn xl:hidden"
                :class="{ 'is-active': isMobileSearchOpen }"
                @click="toggleMobileSearch()"
                aria-label="{{ __('app.search_placeholder') }}">
                <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                </svg>
            </button>

            <button type="button" class="header-icon-btn" @click="$store.theme.toggle()" aria-label="Toggle theme">
                <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415Z" fill="currentColor"/></svg>
                <svg class="h-5 w-5 dark:hidden" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97Z" fill="currentColor"/></svg>
            </button>

                {{-- Alerts dropdown (behaviour handled by resources/js/app.js via .header-alert / .header-alert-toggle) --}}
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
                        {{-- Trigger button --}}
                        <button type="button"
                            data-tour="header-alerts"
                            class="header-alert-toggle header-icon-btn"
                            title="{{ __('app.notifications.title') }}">
                            {{-- Bell icon --}}
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <path d="M12 3.5C9.51472 3.5 7.5 5.51472 7.5 8V10.2344C7.5 11.0897 7.21486 11.9207 6.68945 12.5957L5.73047 13.8291C5.20006 14.5111 5.68643 15.5 6.55078 15.5H17.4492C18.3136 15.5 18.7999 14.5111 18.2695 13.8291L17.3105 12.5957C16.7851 11.9207 16.5 11.0897 16.5 10.2344V8C16.5 5.51472 14.4853 3.5 12 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.5 17.5C9.80616 18.3734 10.6383 19 11.625 19H12.375C13.3617 19 14.1938 18.3734 14.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>

                            {{-- Counter badge --}}
                            <span
                                class="js-header-alert-count absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-error-500 px-1 text-[10px] font-semibold text-white {{ $alertCount ? '' : 'hidden' }}">
                                {{ $alertCount }}
                            </span>
                        </button>

                        {{-- Dropdown tray --}}
                        <div
                            class="header-alert-menu fixed inset-x-4 top-[3.75rem] z-40 flex max-h-[70vh] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white text-sm shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 xl:absolute xl:inset-x-auto xl:right-0 xl:top-full xl:mt-2 xl:w-[27rem] xl:max-h-none">
                            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ __('app.notifications.title') }}
                                    </h3>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"><span class="js-header-alert-active-count">{{ $alertCount }}</span> {{ __('app.notifications.active') }}</p>
                                </div>
                                <button type="button"
                                        class="header-alert-menu-close flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                                        aria-label="Close alerts">
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
                                            <a href="{{ $openUrl }}"
                                                class="header-alert-action group block rounded-xl border px-3 py-2.5 transition hover:shadow-sm {{ $alertStyles['ring'] }}">
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
                                <a href="{{ route('admin.notifications.index') }}"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    {{ __('app.notifications.view_all') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endauth

            {{-- User dropdown --}}
            @auth
                @php
                    $user = auth()->user();
                    $user->loadMissing('employee');
                    $labelSource = $user->name ?: $user->email;
                    $initials = strtoupper(mb_substr($labelSource, 0, 2));
                    $role = $user->role ?? 'employee';
                    $roleLabels = __('app.roles');
                    $avatarUrl = $user->employee && $user->employee->photo_path
                        ? asset('storage/' . $user->employee->photo_path)
                        : null;
                @endphp
                <div class="header-user relative ml-0.5">
                    <button type="button"
                        data-tour="header-user"
                        class="header-user-toggle header-user-toggle--avatar"
                        aria-label="{{ $user->name ?? $user->email }}">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ $initials }}
                        @endif
                    </button>

                    <div
                        class="header-user-menu absolute right-0 top-full z-40 mt-2 w-64 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-3 flex items-center gap-3 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white overflow-hidden">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Profile photo" class="h-full w-full object-cover">
                                @else
                                    {{ $initials }}
                                @endif
                            </span>
                            <div class="space-y-0.5">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $user->name ?? $user->email }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->email }}
                                </div>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $roleLabels[$role] ?? strtoupper(str_replace('_', ' ', $role)) }}
                                </span>
                            </div>
                        </div>

                        <ul class="mb-2 space-y-1 text-[13px] text-gray-700 dark:text-gray-300">
                            <li>
                                <a href="{{ route('admin.profile.edit') }}"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <span>{{ __('app.user_menu.edit_profile') }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="mb-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                            <p class="mb-2 px-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {{ __('app.language.switch_label') }}
                            </p>
                            <x-locale-toggle class="locale-toggle--menu" />
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center justify-center rounded-lg bg-error-50 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-100 dark:bg-error-500/10 dark:text-error-300 dark:hover:bg-error-500/20">
                                {{ __('app.user_menu.sign_out') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>

    <div x-show="isMobileSearchOpen" x-cloak class="app-header__mobile-search">
        <x-layout.header-search :items="$__menuSearchItems" class="w-full" />
    </div>
</header>
