{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'SAFERP'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-outfit bg-gray-50 antialiased dark:bg-gray-950">
    {{-- Simple guest layout without sidebar, header, or authenticated UI --}}
    <div class="flex min-h-screen flex-col">
        {{-- Optional minimal header for guest pages --}}
        <header class="absolute left-0 right-0 top-0 z-50">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                {{-- Brand --}}
                <a href="{{ route('login') }}" class="text-title-sm font-semibold text-gray-900 dark:text-white">
                    {{ config('app.name', 'SAFERP') }}
                </a>

                {{-- Right side actions --}}
                <div class="flex items-center gap-3">
                    {{-- Dark/Light mode toggle --}}
                    <button id="theme-toggle" 
                            type="button" 
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300"
                            aria-label="Toggle dark mode">
                        {{-- Sun icon (light mode) --}}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="block dark:hidden">
                            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 2V4M12 20V22M4 12H2M6.34315 6.34315L4.92893 4.92893M17.6569 6.34315L19.0711 4.92893M6.34315 17.6569L4.92893 19.0711M17.6569 17.6569L19.0711 19.0711M22 12H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        {{-- Moon icon (dark mode) --}}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="hidden dark:block">
                            <path d="M12 3C10.6868 3 9.38642 3.25866 8.17317 3.7612C6.95991 4.26375 5.85752 5.00035 4.92893 5.92893C3.05357 7.8043 2 10.3478 2 13C2 15.6522 3.05357 18.1957 4.92893 20.0711C5.85752 20.9997 6.95991 21.7362 8.17317 22.2388C9.38642 22.7413 10.6868 23 12 23C14.6522 23 17.1957 21.9464 19.0711 20.0711C20.9464 18.1957 22 15.6522 22 13C22 12.6868 21.985 12.3742 21.955 12.064C20.9898 12.7882 19.8256 13.1787 18.6307 13.1787C15.5359 13.1787 13.0225 10.6653 13.0225 7.57052C13.0225 6.10801 13.6212 4.70255 14.649 3.68696C13.8402 3.24472 12.9384 3 12 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                            Create account
                        </a>
                    @endif
                </div>
            </div>
        </header>

        {{-- Main content --}}
        <main class="flex flex-1 items-center justify-center px-4 py-24 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        {{-- Simple footer for guest pages --}}
        <footer class="py-6 text-center">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'SAFERP') }}. All rights reserved.
            </p>
        </footer>
    </div>

    {{-- Theme toggle script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    // Check if dark class is present
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                });
            }

            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                // Check system preference
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            }
        });
    </script>

    {{-- Stack for page-specific scripts --}}
    @stack('scripts')
</body>
</html>