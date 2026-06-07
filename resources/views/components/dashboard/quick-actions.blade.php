@props([
    'title' => 'Quick actions',
])

@php
    $actions = [
        [
            'href' => route('admin.orders.create'),
            'label' => 'New order',
            'hint' => 'Sales order',
            'primary' => true,
            'icon' => 'order',
        ],
        [
            'href' => route('admin.orders.index'),
            'label' => 'Orders',
            'hint' => 'Sales list',
            'icon' => 'orders',
        ],
        [
            'href' => route('admin.orders.picking-overview'),
            'label' => 'Picking',
            'hint' => 'Pick & pack',
            'icon' => 'picking',
        ],
        [
            'href' => route('admin.deliveries.index'),
            'label' => 'Deliveries',
            'hint' => 'Last mile',
            'icon' => 'delivery',
        ],
        [
            'href' => route('admin.deliveries.create'),
            'label' => 'Schedule',
            'hint' => 'New delivery',
            'icon' => 'schedule',
        ],
        [
            'href' => route('admin.finance.index'),
            'label' => 'Invoices',
            'hint' => 'Finance',
            'icon' => 'invoice',
        ],
        [
            'href' => route('admin.production.index'),
            'label' => 'Production',
            'hint' => 'Factory runs',
            'icon' => 'production',
        ],
        [
            'href' => route('admin.inventory.low-stock'),
            'label' => 'Low stock',
            'hint' => 'Reorder alerts',
            'icon' => 'alert',
        ],
        [
            'href' => route('admin.agents.index'),
            'label' => 'Agents',
            'hint' => 'Network',
            'icon' => 'agents',
        ],
        [
            'href' => route('admin.products.index'),
            'label' => 'Products',
            'hint' => 'SKU catalog',
            'icon' => 'products',
        ],
        [
            'href' => route('admin.purchase-orders.create'),
            'label' => 'New PO',
            'hint' => 'Purchase',
            'icon' => 'po',
        ],
        [
            'href' => route('admin.goods-receipts.create'),
            'label' => 'GRN',
            'hint' => 'Goods receipt',
            'icon' => 'grn',
        ],
    ];
@endphp

<section
    {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900']) }}
    x-data="{ open: false }"
>
    <button
        type="button"
        class="flex w-full items-center justify-between gap-4 px-4 py-3.5 text-left transition hover:bg-gray-50/80 sm:px-5 dark:hover:bg-white/[0.02]"
        @click="open = !open"
        :aria-expanded="open"
        aria-controls="dash-quick-actions-panel"
    >
        <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                <span x-show="!open">{{ count($actions) }} daily shortcuts · Click to expand</span>
                <span x-show="open" x-cloak>Sales, logistics, finance, production, and stock</span>
            </p>
        </div>

        <span class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            <span x-text="open ? 'Hide' : 'Show'"></span>
            <svg
                class="h-4 w-4 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </button>

    <div
        id="dash-quick-actions-panel"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="border-t border-gray-100 px-4 pb-4 pt-3 sm:px-5 sm:pb-5 dark:border-gray-800"
    >
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-6">
            @foreach($actions as $action)
                <a
                    href="{{ $action['href'] }}"
                    @class([
                        'group flex min-h-[4.5rem] flex-col items-center justify-center gap-2 rounded-xl border px-2 py-3 text-center transition',
                        'border-brand-200 bg-brand-50 text-brand-700 hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15' => ! empty($action['primary']),
                        'border-gray-200 bg-gray-50/80 text-gray-700 hover:border-gray-300 hover:bg-white dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-gray-800' => empty($action['primary']),
                    ])
                >
                    <span @class([
                        'flex h-9 w-9 items-center justify-center rounded-lg',
                        'bg-brand-500 text-white shadow-sm' => ! empty($action['primary']),
                        'bg-white text-brand-600 ring-1 ring-gray-200 dark:bg-gray-900 dark:text-brand-400 dark:ring-gray-700' => empty($action['primary']),
                    ]) aria-hidden="true">
                        @switch($action['icon'])
                            @case('order')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                @break
                            @case('orders')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                                @break
                            @case('picking')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4" />
                                </svg>
                                @break
                            @case('delivery')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                                </svg>
                                @break
                            @case('schedule')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                @break
                            @case('invoice')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                @break
                            @case('production')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                </svg>
                                @break
                            @case('alert')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                @break
                            @case('inventory')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5a1.125 1.125 0 0 0-1.125-1.125H3.375a1.125 1.125 0 0 0-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                                @break
                            @case('agents')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                @break
                            @case('products')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5a1.125 1.125 0 0 0-1.125-1.125H3.375a1.125 1.125 0 0 0-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                                @break
                            @case('po')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                </svg>
                                @break
                            @default
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.125 1.125 0 0 1 1.125 1.125v11.25a1.125 1.125 0 0 1-1.125 1.125H5.625a1.125 1.125 0 0 1-1.125-1.125V5.625a1.125 1.125 0 0 1 1.125-1.125Z" />
                                </svg>
                        @endswitch
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-xs font-semibold leading-tight">{{ $action['label'] }}</span>
                        <span class="mt-0.5 block truncate text-[10px] text-gray-500 group-hover:text-gray-600 dark:text-gray-400 dark:group-hover:text-gray-300">{{ $action['hint'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
