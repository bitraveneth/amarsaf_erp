@php
    $typeLabels = $typeLabels ?? [
        'asset' => 'Assets',
        'liability' => 'Liabilities',
        'equity' => 'Equity',
        'income' => 'Income',
        'expense' => 'Expenses',
    ];

    $typeThemes = [
        'asset' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/30', 'border' => 'border-emerald-200 dark:border-emerald-800', 'pill' => 'text-emerald-700 bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/40', 'amount' => 'text-emerald-800 dark:text-emerald-200'],
        'liability' => ['bg' => 'bg-orange-50 dark:bg-orange-950/30', 'border' => 'border-orange-200 dark:border-orange-800', 'pill' => 'text-orange-700 bg-orange-100 dark:text-orange-300 dark:bg-orange-900/40', 'amount' => 'text-orange-800 dark:text-orange-200'],
        'equity' => ['bg' => 'bg-blue-50 dark:bg-blue-950/30', 'border' => 'border-blue-200 dark:border-blue-800', 'pill' => 'text-blue-700 bg-blue-100 dark:text-blue-300 dark:bg-blue-900/40', 'amount' => 'text-blue-800 dark:text-blue-200'],
        'income' => ['bg' => 'bg-sky-50 dark:bg-sky-950/30', 'border' => 'border-sky-200 dark:border-sky-800', 'pill' => 'text-sky-700 bg-sky-100 dark:text-sky-300 dark:bg-sky-900/40', 'amount' => 'text-sky-800 dark:text-sky-200'],
        'expense' => ['bg' => 'bg-rose-50 dark:bg-rose-950/30', 'border' => 'border-rose-200 dark:border-rose-800', 'pill' => 'text-rose-700 bg-rose-100 dark:text-rose-300 dark:bg-rose-900/40', 'amount' => 'text-rose-800 dark:text-rose-200'],
    ];

    $isBalanceView = ($viewMode ?? 'structure') === 'balance';
    $cards = $isBalanceView ? ($typeCards ?? collect()) : ($structureTypeCards ?? collect());
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ $isBalanceView ? 'Balances by type' : 'Accounts by type' }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($isBalanceView)
                    {{ $periodLabel ?? '' }} · click a type to filter the tree
                @else
                    {{ $stats['total'] }} accounts · click a type to filter the tree
                @endif
            </p>
        </div>
        @if($selectedType)
            <a href="{{ route('admin.accounts.index', array_filter(array_merge($filterQuery ?? [], ['type' => null]))) }}"
               class="rounded-lg bg-brand-50 px-2 py-1 text-[10px] font-semibold uppercase text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                Clear filter
            </a>
        @endif
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @foreach($typeLabels as $typeKey => $typeLabel)
            @php
                $theme = $typeThemes[$typeKey];
                $card = $cards[$typeKey] ?? [];
                $isActive = $selectedType === $typeKey;
                $cardQuery = array_filter(array_merge($filterQuery ?? [], ['type' => $typeKey]));
            @endphp
            <a href="{{ route('admin.accounts.index', $cardQuery) }}"
               class="block rounded-xl border {{ $theme['border'] }} {{ $theme['bg'] }} p-4 transition hover:shadow-md {{ $isActive ? 'ring-2 ring-brand-500 ring-offset-2 dark:ring-offset-gray-900' : '' }}">
                <span class="text-[11px] font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $typeLabel }}</span>

                @if($isBalanceView)
                    <p class="mt-3 text-xl font-bold {{ $theme['amount'] }}">
                        {{ $currencyCode ?? 'BDT' }} {{ number_format($card['balance'] ?? 0, 0) }}
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                        {{ ($card['balance_mode'] ?? 'closing') === 'period' ? 'Period total' : 'Closing balance' }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $card['active_in_period'] ?? 0 }} active ledgers
                    </p>
                @else
                    <p class="mt-3 text-3xl font-bold {{ $theme['amount'] }}">{{ $card['count'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $card['groups'] ?? 0 }} groups · {{ $card['ledgers'] ?? 0 }} ledgers
                    </p>
                @endif
            </a>
        @endforeach
    </div>
</div>
