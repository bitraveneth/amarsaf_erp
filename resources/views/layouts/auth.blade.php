<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'bn' ? 'bn' : 'en' }}" class="h-full" @if(app()->getLocale() === 'bn') data-locale="bn" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in | ' . ($appBrandName ?? config('app.name')))</title>

    <script>
        window.erpLocale = @json(app()->getLocale());
        window.erpLocaleUrl = @json(Route::has('locale.update') ? route('locale.update') : null);
        window.erpDefaultThemeMode = @json($defaultThemeMode ?? 'light');
        window.erpTheme = {
            getStoredTheme() {
                try { return localStorage.getItem('theme'); } catch (e) { return null; }
            },
            setStoredTheme(theme) {
                try { localStorage.setItem('theme', theme); } catch (e) {}
            },
            resolveTheme() {
                const saved = this.getStoredTheme();
                const configured = window.erpDefaultThemeMode || 'light';
                const fallback = configured === 'system'
                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    : configured;
                return saved || fallback;
            },
            applyTheme(theme) {
                const html = document.documentElement;
                const body = document.body;
                if (theme === 'dark') {
                    html.classList.add('dark');
                    html.style.colorScheme = 'dark';
                    if (body) body.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                    html.style.colorScheme = 'light';
                    if (body) body.classList.remove('dark');
                }
            }
        };
        window.erpTheme.applyTheme(window.erpTheme.resolveTheme());
    </script>

    @php($useBuiltAssets = app()->environment('local') && file_exists(public_path('build/manifest.json')))
    @if($useBuiltAssets)
        @php($manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true))
        <link rel="stylesheet" href="{{ asset('build/'.$manifest['resources/css/app.css']['file']) }}">
        <script type="module" src="{{ asset('build/'.$manifest['resources/js/auth-login.js']['file']) }}"></script>
    @else
        @vite(['resources/css/app.css', 'resources/js/auth-login.js'])
    @endif

    @if(!empty($brandThemeVariables))
        <style>
            :root {
                @foreach($brandThemeVariables as $variable => $value)
                    {{ $variable }}: {{ $value }};
                @endforeach
            }
        </style>
    @endif

    @stack('styles')
</head>
<body class="auth-shell theme-text-scope font-outfit antialiased">
    <div class="auth-shell__backdrop" aria-hidden="true">
        <div class="auth-shell__orb auth-shell__orb--one"></div>
        <div class="auth-shell__orb auth-shell__orb--two"></div>
        <div class="auth-shell__orb auth-shell__orb--three"></div>
    </div>

    <div class="auth-shell__page">
        <header class="auth-shell__page-bar">
            <div class="auth-shell__toolbar">
                <x-locale-toggle-auth class="locale-toggle--auth" />
                <x-layout.header-theme-toggle vanilla class="header-theme-toggle--auth" />
            </div>
        </header>

        <main class="auth-shell__card">
            <a href="{{ route('login') }}" class="auth-shell__card-brand">
                <x-brand-mark variant="login" name-mode="none" />
                <span class="auth-shell__brand-name">{{ $appBrandName ?? config('app.name') }}</span>
            </a>

            <div class="auth-shell__form-wrap">
                @yield('content')
            </div>

            <footer class="auth-shell__footer">
                <span>&copy; {{ date('Y') }} {{ $legalCompanyName ?? $appBrandName ?? config('app.name') }}</span>
            </footer>
        </main>

        <p class="auth-shell__tagline">
            @if(app()->getLocale() === 'bn')
                বিক্রয়, স্টক ও হিসাব — এক জায়গায় পরিচালনা করুন।
            @else
                Sales, inventory &amp; finance — all in one place.
            @endif
        </p>
    </div>

    @stack('scripts')

    @stack('scripts')
</body>
</html>
