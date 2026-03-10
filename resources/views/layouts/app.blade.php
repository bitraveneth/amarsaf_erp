@php
    require resource_path('views/layouts/partials/system-tour-steps.php');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? (config('app.name') . ' Admin') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

    <script>
        window.erpTourSteps = @json($tourSteps);
    </script>

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
            });

            Alpine.store('loader', {
                show: true,
                fallbackTimer: null,
                init() {
                    const hideSoon = () => {
                        window.setTimeout(() => this.hide(), 150);
                    };

                    if (document.readyState === 'complete') {
                        hideSoon();
                    } else {
                        window.addEventListener('load', hideSoon, { once: true });
                    }

                    this.fallbackTimer = window.setTimeout(() => {
                        this.hide();
                    }, 2000);
                },
                hide() {
                    if (!this.show) {
                        return;
                    }

                    if (this.fallbackTimer) {
                        window.clearTimeout(this.fallbackTimer);
                        this.fallbackTimer = null;
                    }

                    this.show = false;
                    window.dispatchEvent(new CustomEvent('app:content-visible'));
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
        [x-cloak] {
            display: none !important;
        }

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

        .tour-target-active {
            position: relative;
            z-index: 100003 !important;
            border-radius: 18px;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2), 0 18px 50px rgba(15, 23, 42, 0.28);
        }
    </style>
</head>

<body x-data
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
        window.addEventListener('resize', checkMobile);">

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

    {{-- Main content renders behind the loader overlay --}}
    <div class="min-h-screen xl:flex">
        
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
             :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded,
                'xl:ml-[90px]': !$store.sidebar.isExpanded,
                'ml-0': $store.sidebar.isMobileOpen
             }">
            @include('layouts.app-header')

            <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6" data-tour="page-content">
                @if(session('status'))
                    <div x-data="{ open: true }"
                         x-init="setTimeout(() => open = false, 2600)"
                         x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="fixed right-5 top-20 z-[1000] w-full max-w-sm rounded-2xl border border-brand-200 bg-white p-4 shadow-2xl dark:border-brand-500/30 dark:bg-gray-900">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-brand-700 dark:text-brand-300">Success</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ session('status') }}</div>
                            </div>
                            <button type="button"
                                    @click="open = false"
                                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if(($errors ?? null) && $errors->any())
                    <div class="mb-4">
                        <div class="rounded-lg border border-error-100 bg-error-50 px-4 py-3 text-sm text-error-700">
                            <strong class="font-semibold">Something went wrong.</strong>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <div class="fixed bottom-5 right-5 z-[1001]" x-data>
        <div class="flex flex-col items-end gap-3">
            <div x-show="$store.tour.launcherOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="w-[22rem] rounded-3xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Understand the ERP cycle</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Follow the real business flow: setup, procurement, production, inventory, sales, accounting, and reporting.
                        </p>
                    </div>
                    <button type="button"
                            @click="$store.tour.closeLauncher()"
                            class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 rounded-2xl bg-gray-50 px-4 py-3 text-xs text-gray-600 dark:bg-gray-800/80 dark:text-gray-300">
                    <div class="font-semibold text-gray-800 dark:text-white">Tour path</div>
                    <div class="mt-1 leading-5">Control setup → Purchase orders → GRN → BOM → Production → Inventory → Orders → Invoices → Reconciliation → Reports</div>
                </div>

                <div class="mt-4 space-y-2">
                    <button type="button"
                            @click="$store.tour.start()"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
                        <span x-text="$store.tour.hasSavedProgress ? 'Restart system tour' : 'Start full system tour'"></span>
                    </button>
                    <button type="button"
                            x-show="$store.tour.hasSavedProgress"
                            x-cloak
                            @click="$store.tour.resumeIfNeeded(); $store.tour.closeLauncher()"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        Resume saved step
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                       class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        Start with products
                    </a>
                    @if(Route::has('admin.client-guide'))
                        <a href="{{ route('admin.client-guide') }}"
                           class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                            Open full manual
                        </a>
                    @endif
                </div>
            </div>

            <button type="button"
                    data-tour="help-launcher"
                    @click="$store.tour.toggleLauncher()"
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-500 text-xl font-semibold text-white shadow-2xl transition hover:bg-brand-600">
                ?
            </button>
        </div>
    </div>

    <div x-show="$store.tour.isActive"
         x-cloak
         class="fixed inset-0 z-[100002]">
        <div x-show="$store.tour.spotlight.width > 0 && $store.tour.spotlight.height > 0" class="absolute inset-0 bg-gray-950/55"></div>

        <div x-show="$store.tour.spotlight.width > 0 && $store.tour.spotlight.height > 0"
             class="pointer-events-none absolute rounded-[28px] border-2 border-white/80 shadow-[0_0_0_9999px_rgba(3,7,18,0.55)] transition-all duration-200"
             :style="`top:${$store.tour.spotlight.top}px;left:${$store.tour.spotlight.left}px;width:${$store.tour.spotlight.width}px;height:${$store.tour.spotlight.height}px;`">
        </div>

        <div data-tour-tooltip
             class="pointer-events-auto absolute z-[100004] w-[24rem] rounded-3xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900"
             :style="`top:${$store.tour.tooltip.top};left:${$store.tour.tooltip.left};right:${$store.tour.tooltip.right};bottom:${$store.tour.tooltip.bottom};`">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-500">
                        Step <span x-text="$store.tour.activeIndex + 1"></span>
                        of <span x-text="$store.tour.steps.length"></span>
                    </div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-gray-400"
                         x-text="$store.tour.currentStep()?.section ?? ''">
                    </div>
                    <h3 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white"
                        x-text="$store.tour.currentStep()?.title ?? ''">
                    </h3>
                </div>
                <button type="button"
                        @click="$store.tour.end()"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 space-y-4 text-[15px] leading-7 text-gray-600 dark:text-gray-300">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-400">Why this matters</div>
                    <p class="mt-1" x-text="$store.tour.currentStep()?.purpose ?? ''"></p>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-400">What to do here</div>
                    <p class="mt-1" x-text="$store.tour.currentStep()?.action ?? ''"></p>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3">
                <button type="button"
                        @click="$store.tour.previous()"
                        :disabled="$store.tour.activeIndex === 0"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                    Previous
                </button>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="$store.tour.end()"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        Skip
                    </button>
                    <button type="button"
                            @click="$store.tour.next()"
                            class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
                        <span x-text="$store.tour.activeIndex === ($store.tour.steps.length - 1) ? 'Finish' : 'Next'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

</body>
</html>
