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
        $profileUrl = route('admin.profile.edit');
        $displayName = trim((string) ($user->name ?? ''));
        $shortName = $displayName !== ''
            ? explode(' ', $displayName)[0]
            : explode('@', (string) $user->email)[0];
    @endphp

    <div class="header-user relative">
        <button
            type="button"
            data-tour="header-user"
            class="header-user-toggle"
            aria-label="{{ $user->name ?? $user->email }}"
            aria-haspopup="true"
        >
            <span class="header-user-toggle__avatar">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover">
                @else
                    {{ $initials }}
                @endif
            </span>
            <span class="header-user-toggle__name">{{ $shortName }}</span>
            <svg class="header-user-toggle__chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="header-user-menu absolute right-0 top-full z-40 mt-2.5 w-[17.5rem] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
            <a href="{{ $profileUrl }}" class="header-user-menu__profile">
                <span class="header-user-menu__avatar">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover">
                    @else
                        {{ $initials }}
                    @endif
                </span>
                <span class="min-w-0 flex-1">
                    <span class="header-user-menu__name">{{ $user->name ?? $user->email }}</span>
                    <span class="header-user-menu__email">{{ $user->email }}</span>
                    <span class="header-user-menu__role">
                        {{ $roleLabels[$role] ?? strtoupper(str_replace('_', ' ', $role)) }}
                    </span>
                </span>
            </a>

            <div class="header-user-menu__divider" aria-hidden="true"></div>

            <div class="px-2 pb-2">
                <a href="{{ $profileUrl }}" class="header-user-menu__item">
                    <svg class="header-user-menu__icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M10 10.625a3.125 3.125 0 1 0 0-6.25 3.125 3.125 0 0 0 0 6.25Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M4.375 16.875c0-2.875 2.525-5 5.625-5s5.625 2.125 5.625 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span>{{ __('app.user_menu.edit_profile') }}</span>
                </a>

                <div class="header-user-menu__divider" aria-hidden="true"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="header-user-menu__signout">
                        <svg class="header-user-menu__icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M7.5 9.375 4.375 12.5 7.5 15.625" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.375 12.5H12.5M12.5 5.625V4.375A1.875 1.875 0 0 0 10.625 2.5H5.625A1.875 1.875 0 0 0 3.75 4.375v11.25A1.875 1.875 0 0 0 5.625 17.5h5A1.875 1.875 0 0 0 12.5 15.625v-1.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>{{ __('app.user_menu.sign_out') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endauth
