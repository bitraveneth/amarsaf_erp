@extends('layouts.app')

@section('content')
<div class="space-y-8"
     x-data="{
        showAccount: false,
        selectedAccount: null,
     }">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Chart of Accounts Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                </div>

                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Chart of Accounts
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Maintain the list of GL accounts used in ledger entries and reports
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.accounts.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Account
            </a>
        </div>
    </div>

    <!-- Status Message -->
    @if(session('status'))
        <div class="rounded-xl border-l-4 border-success-500 bg-success-50 p-4 dark:border-success-400 dark:bg-success-950/30">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-success-700 dark:text-success-400">{{ session('status') }}</p>
            </div>
        </div>
    @endif

    @if($accounts->isNotEmpty())
        @php
            $totalAccounts = $accounts->count();
            $activeAccounts = $accounts->where('is_active', true)->count();
            $accountTypes = $accounts->groupBy('type')->map->count();
        @endphp

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-5">
            <!-- Equity Accounts -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Accounts</span>
                        <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalAccounts }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-success-600 dark:text-success-400">{{ $activeAccounts }}</span> active,
                        <span class="font-medium text-gray-600 dark:text-gray-400">{{ $totalAccounts - $activeAccounts }}</span> inactive
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Asset Accounts</span>
                        <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                            <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-success-600 dark:text-success-400">
                        {{ $accountTypes['asset'] ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Liability Accounts</span>
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {{ $accountTypes['liability'] ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Equity Accounts</span>
                        <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-brand-600 dark:text-brand-400">
                        {{ $accountTypes['equity'] ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- Income Accounts -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.5 3.75h15v2.25h-15zM7.5 8.25h9v2.25h-9zM10.5 12.75h3v2.25h-3zM6 17.25h12v3H6z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Income Accounts</span>
                        <div class="rounded-lg bg-blue-light-100 p-2 dark:bg-blue-light-900/30">
                            <svg class="h-4 w-4 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M5 11l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-blue-light-600 dark:text-blue-light-400">
                        {{ $accountTypes['income'] ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- Expense Accounts -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 4.75h14v2.5H5zM5 10.75h9v2.5H5zM5 16.75h6v2.5H5z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Expense Accounts</span>
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <svg class="h-4 w-4 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-purple-600 dark:text-purple-400">
                        {{ $accountTypes['expense'] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Account Cards Grid View -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($accounts as $account)
                @php
                    $typeColors = [
                        'asset'    => ['bg' => 'bg-success-50',    'badge' => 'bg-success-100 text-success-800',    'icon' => 'bg-success-100',    'iconColor' => 'text-success-600',    'gradient' => 'from-success-500 to-success-600'],
                        'liability'=> ['bg' => 'bg-orange-50',     'badge' => 'bg-orange-100 text-orange-800',    'icon' => 'bg-orange-100',     'iconColor' => 'text-orange-600',     'gradient' => 'from-orange-500 to-orange-600'],
                        'equity'   => ['bg' => 'bg-brand-50',      'badge' => 'bg-brand-100 text-brand-800',      'icon' => 'bg-brand-100',      'iconColor' => 'text-brand-600',      'gradient' => 'from-brand-500 to-brand-600'],
                        'income'   => ['bg' => 'bg-blue-light-50', 'badge' => 'bg-blue-light-100 text-blue-light-800', 'icon' => 'bg-blue-light-100', 'iconColor' => 'text-blue-light-600', 'gradient' => 'from-blue-light-500 to-blue-light-600'],
                        'expense'  => ['bg' => 'bg-purple-50',     'badge' => 'bg-purple-100 text-purple-800',    'icon' => 'bg-purple-100',     'iconColor' => 'text-purple-600',     'gradient' => 'from-purple-500 to-purple-600'],
                    ];

                    $typeKey = $account->type ?? 'other';
                    $colors = $typeColors[$typeKey] ?? [
                        'bg'       => 'bg-gray-50',
                        'badge'    => 'bg-gray-100 text-gray-800',
                        'icon'     => 'bg-gray-100',
                        'iconColor'=> 'text-gray-600',
                        'gradient' => 'from-gray-500 to-gray-600',
                    ];

                    // Mock ledger data for demo – replace with real entries later.
                    $ledgerEntries = [
                        ['date' => '2024-01-15', 'description' => 'Opening Balance',         'debit' => 5000, 'credit' => 0,    'balance' => 5000],
                        ['date' => '2024-01-20', 'description' => 'Sale Invoice INV-001',   'debit' => 0,    'credit' => 1200, 'balance' => 3800],
                        ['date' => '2024-01-25', 'description' => 'Payment Received',       'debit' => 800,  'credit' => 0,    'balance' => 4600],
                        ['date' => '2024-02-01', 'description' => 'Purchase Payment',       'debit' => 0,    'credit' => 2000, 'balance' => 2600],
                    ];

                    $totalDebit      = array_sum(array_column($ledgerEntries, 'debit'));
                    $totalCredit     = array_sum(array_column($ledgerEntries, 'credit'));
                    $currentBalance  = end($ledgerEntries)['balance'] ?? 0;
                @endphp

                <!-- Account Card -->
                <div
                    class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden"
                    @click="selectedAccount = @js([
                        'id'              => $account->id,
                        'code'            => $account->code,
                        'name'            => $account->name,
                        'type'            => $account->type,
                        'type_color'      => $colors['badge'],
                        'is_active'       => (bool) $account->is_active,
                        'status_label'    => $account->is_active ? 'Active' : 'Inactive',
                        'status_color'    => $account->is_active ? 'bg-success-50 text-success-700' : 'bg-gray-100 text-gray-700',
                        'description'     => $account->description ?? 'No description provided',
                        'created'         => optional($account->created_at)->format('d M Y'),
                        'updated'         => optional($account->updated_at)->diffForHumans(),
                        'total_debit'     => $totalDebit,
                        'total_credit'    => $totalCredit,
                        'current_balance' => $currentBalance,
                        'ledger_entries'  => $ledgerEntries,
                    ]); showAccount = true">

                    <!-- Hover overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br {{ $colors['gradient'] }} opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>

                    <!-- Card content -->
                    <div class="p-6 relative">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <!-- Icon -->
                                <div class="relative">
                                    <div class="absolute -inset-1 {{ $colors['bg'] }} rounded-lg opacity-50"></div>
                                    <div class="relative flex h-12 w-12 items-center justify-center rounded-lg {{ $colors['icon'] }} {{ $colors['iconColor'] }}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($account->type === 'asset')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                                            @elseif($account->type === 'liability')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                                            @elseif($account->type === 'equity')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                            @endif
                                        </svg>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $account->name }}</h3>
                                    <p class="font-mono text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $account->code }}</p>
                                </div>
                            </div>

                            <!-- Type Badge -->
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize {{ $colors['badge'] }}">
                                {{ $account->type }}
                            </span>
                        </div>

                        <!-- Description -->
                        @if($account->description)
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $account->description }}</p>
                        @endif

                        <!-- Quick stats -->
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Current Balance</p>
                                <p class="text-lg font-bold {{ $currentBalance >= 0 ? 'text-success-600' : 'text-error-600' }}">
                                    ৳ {{ number_format($currentBalance, 2) }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="h-2 w-2 rounded-full {{ $account->is_active ? 'bg-success-500' : 'bg-gray-500' }}"></span>
                                    <span class="text-sm font-medium {{ $account->is_active ? 'text-success-600' : 'text-gray-600' }}">
                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Activity summary -->
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 dark:text-gray-400">Debit:</span>
                                <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($totalDebit, 0) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 dark:text-gray-400">Credit:</span>
                                <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($totalCredit, 0) }}</span>
                            </div>
                        </div>

                        <!-- Hint -->
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs text-brand-600 dark:text-brand-400 group-hover:underline">Click to view full ledger</span>
                            <svg class="h-4 w-4 text-gray-400 group-hover:text-brand-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Bottom bar -->
                    <div class="h-1 w-full bg-gradient-to-r {{ $colors['gradient'] }}"></div>
                </div>
            @endforeach
        </div>

        @if(method_exists($accounts, 'links'))
            <div class="mt-6">
                {{ $accounts->links() }}
            </div>
        @endif

        {{-- Slide-over with ledger --}}
        <div
            x-show="showAccount && selectedAccount"
            x-cloak
            class="fixed inset-0 z-[9999] overflow-y-auto"
            @keydown.escape.window="showAccount = false"
            style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);">
            <div class="flex min-h-full items-end justify-end p-0 sm:items-center sm:p-0">
                <div
                    x-show="showAccount && selectedAccount"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-full"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 translate-x-full"
                    class="relative w-full max-w-4xl bg-white dark:bg-gray-900 shadow-2xl sm:rounded-2xl sm:mx-6 my-0 sm:my-8"
                    @click.outside="showAccount = false">

                    <!-- Close -->
                    <button type="button"
                            class="absolute top-4 right-4 z-10 rounded-full p-2 bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 shadow-md hover:shadow-lg transition-all"
                            @click="showAccount = false">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 6l8 8M14 6l-8 8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <!-- Header -->
                    <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-8 py-6 sm:rounded-t-2xl">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm text-white">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-white/80">Account Details &amp; Ledger</p>
                                    <h2 class="mt-1 text-2xl font-bold text-white" x-text="selectedAccount.name"></h2>
                                    <div class="mt-1 flex items-center gap-3">
                                        <span class="font-mono text-sm text-white/90">Code: <span class="font-semibold" x-text="selectedAccount.code"></span></span>
                                        <span class="w-1 h-1 rounded-full bg-white/40"></span>
                                        <span class="text-sm text-white/90">Type: <span class="font-semibold capitalize" x-text="selectedAccount.type"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-8 py-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                        <!-- Quick stats -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Current Balance</span>
                                    <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-2xl font-bold"
                                   :class="selectedAccount.current_balance >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400'"
                                   x-text="'৳ ' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(selectedAccount.current_balance)"></p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">As of today</p>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Account Type</span>
                                    <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                        </svg>
                                    </div>
                                </div>
                                <span class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize"
                                      :class="selectedAccount.type_color">
                                    <span class="mr-1.5 h-2 w-2 rounded-full bg-current opacity-70"></span>
                                    <span x-text="selectedAccount.type"></span>
                                </span>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</span>
                                    <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium"
                                          :class="selectedAccount.status_color">
                                        <span class="h-2 w-2 rounded-full"
                                              :class="selectedAccount.is_active ? 'bg-success-500' : 'bg-gray-500'"></span>
                                        <span x-text="selectedAccount.status_label"></span>
                                    </span>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Updated</span>
                                    <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white" x-text="selectedAccount.updated"></p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-8" x-show="selectedAccount.description">
                            <div class="rounded-xl bg-brand-50 dark:bg-brand-900/10 border border-brand-100 dark:border-brand-900/20 p-5">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-brand-700 dark:text-brand-400 leading-relaxed" x-text="selectedAccount.description"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Ledger -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span class="w-1 h-5 bg-brand-500 rounded-full"></span>
                                    Ledger Entries
                                </h3>
                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 dark:text-gray-400">Total Debit:</span>
                                        <span class="font-semibold text-success-600 dark:text-success-400">৳ <span x-text="new Intl.NumberFormat('en-US').format(selectedAccount.total_debit)"></span></span>
                                    </div>
                                    <div class="w-px h-4 bg-gray-300 dark:bg-gray-700"></div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500 dark:text-gray-400">Total Credit:</span>
                                        <span class="font-semibold text-error-600 dark:text-error-400">৳ <span x-text="new Intl.NumberFormat('en-US').format(selectedAccount.total_credit)"></span></span>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                                <table class="w-full">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Date</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Description</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Debit (৳)</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Credit (৳)</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Balance (৳)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="(entry, index) in selectedAccount.ledger_entries" :key="index">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-mono" x-text="entry.date"></td>
                                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" x-text="entry.description"></td>
                                                <td class="px-6 py-4 text-sm text-right font-mono text-gray-900 dark:text-white" x-text="entry.debit ? '৳ ' + new Intl.NumberFormat('en-US').format(entry.debit) : '—'"></td>
                                                <td class="px-6 py-4 text-sm text-right font-mono text-gray-900 dark:text-white" x-text="entry.credit ? '৳ ' + new Intl.NumberFormat('en-US').format(entry.credit) : '—'"></td>
                                                <td class="px-6 py-4 text-sm text-right font-mono font-medium"
                                                    :class="entry.balance >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400'"
                                                    x-text="'৳ ' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(entry.balance)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-800/50 border-t-2 border-gray-300 dark:border-gray-700">
                                        <tr>
                                            <td colspan="2" class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">Current Balance</td>
                                            <td class="px-6 py-4 text-sm text-right font-semibold text-success-600 dark:text-success-400" x-text="'৳ ' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(selectedAccount.total_debit)"></td>
                                            <td class="px-6 py-4 text-sm text-right font-semibold text-error-600 dark:text-error-400" x-text="'৳ ' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(selectedAccount.total_credit)"></td>
                                            <td class="px-6 py-4 text-sm text-right font-bold text-brand-600 dark:text-brand-400" x-text="'৳ ' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(selectedAccount.current_balance)"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 dark:border-gray-800 px-8 py-4 bg-gray-50 dark:bg-gray-800/50 sm:rounded-b-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Created: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedAccount.created"></span></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <a :href="'{{ url('/admin/accounts') }}/' + selectedAccount.id + '/edit'"
                                   class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Account
                                </a>
                                <button type="button"
                                        class="inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        @click="showAccount = false">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm p-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 opacity-20 dark:from-gray-900 dark:to-gray-800 blur-3xl"></div>

            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-500 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">No Accounts Defined</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    No general ledger accounts have been created yet. Add your first account to start building your chart of accounts.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <a href="{{ route('admin.accounts.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add First Account
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
