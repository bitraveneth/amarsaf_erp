<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? (config('app.name') . ' Admin') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                },
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                },
            });

            Alpine.store('loader', {
                show: true,
                init() {
                    // Wait for page to be fully loaded
                    window.addEventListener('load', () => {
                        setTimeout(() => {
                            this.show = false;
                        }, 500); // Smooth fade out after load
                    });
                    
                    // Fallback: hide after 3 seconds max
                    setTimeout(() => {
                        this.show = false;
                    }, 3000);
                },
                hide() {
                    this.show = false;
                }
            });
        });
    </script>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>

    <style>
        /* Loader animations */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .loader-fade-leave {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            pointer-events: none;
        }
    </style>
</head>

<body x-data="{ loaded: false }"
      x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
        const checkMobile = () => {
            if (window.innerWidth < 1280) {
                $store.sidebar.setMobileOpen(false);
                $store.sidebar.isExpanded = false;
            } else {
                $store.sidebar.isMobileOpen = false;
                $store.sidebar.isExpanded = true;
            }
        };
        window.addEventListener('resize', checkMobile);
        
        // Set loaded to true after Alpine is initialized
        setTimeout(() => { loaded = true; }, 100);">

    {{-- Page Loader --}}
    <div x-show="$store.loader.show" 
         x-transition:leave="loader-fade-leave"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] flex flex-col items-center justify-center bg-white dark:bg-gray-950"
         style="will-change: opacity;">
        
        {{-- Logo with pulse animation --}}
        <div class="mb-6 animate-pulse">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-brand-50 shadow-theme-md dark:bg-brand-500/10">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-brand-600 dark:text-brand-400">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        {{-- App name with slide animation --}}
        <h1 class="mb-8 text-title-md font-semibold text-gray-900 dark:text-white animate-[slideUp_0.6s_ease-out]">
            {{ config('app.name') }}
        </h1>

        {{-- Bouncing dots loader --}}
        <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-brand-500 animate-bounce" style="animation-delay: 0ms;"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-brand-500/80 animate-bounce" style="animation-delay: 150ms;"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-brand-500/60 animate-bounce" style="animation-delay: 300ms;"></span>
        </div>

        {{-- Loading status messages --}}
        <p class="mt-8 text-theme-sm text-gray-600 dark:text-gray-400 animate-pulse">
            <span x-show="$store.loader.show" x-text="[
                'Preparing your dashboard...',
                'Loading modules...',
                'Almost there...',
                'Welcome back!'
            ][Math.floor(Math.random() * 4)]" class="inline-block min-w-[200px] text-center">
                Loading...
            </span>
        </p>

        {{-- Quick tip for demo users --}}
        @if(app()->environment('local'))
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 rounded-lg bg-gray-100 px-4 py-2 text-theme-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                ⚡ Demo environment • Data resets daily
            </div>
        @endif
    </div>

    {{-- Main content - hidden until loader is done --}}
    <div x-show="!$store.loader.show" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="min-h-screen xl:flex"
         style="display: none;">
        
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
             :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
             }">
            @include('layouts.app-header')

            <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                @if(session('status') || ($errors ?? null) && $errors->any())
                    <div class="mb-4 space-y-3">
                        @if(session('status'))
                            <div class="rounded-lg border border-success-100 bg-success-50 px-4 py-3 text-sm text-success-700">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if(($errors ?? null) && $errors->any())
                            <div class="rounded-lg border border-error-100 bg-error-50 px-4 py-3 text-sm text-error-700">
                                <strong class="font-semibold">Something went wrong.</strong>
                                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        // Force hide loader after maximum wait time
        setTimeout(() => {
            const root = document.querySelector('body[x-data]');
            if (root && root.__x && root.__x.$store?.loader?.show) {
                root.__x.$store.loader.hide();
            }
        }, 4000);
    </script>
</body>
</html>
