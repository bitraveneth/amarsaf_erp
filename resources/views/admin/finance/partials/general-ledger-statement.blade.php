@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $periodParams = request()->only(['range', 'from', 'to']);
    $activeCount = ($activeAccounts ?? collect())->count();
    $netMovement = round($periodDebit - $periodCredit, 2);
    $movementTone = $netMovement >= 0 ? 'up' : 'down';

    $breadcrumb = null;
    if ($selectedAccount) {
        $crumbs = $selectedAccount->ancestors()->pluck('name')->filter()->values();
        $breadcrumb = $crumbs->isNotEmpty() ? $crumbs->implode(' › ') : null;
    }

    $accountsForPicker = ($accountOptions ?? collect())->map(fn ($account) => [
        'id' => $account->id,
        'code' => $account->code,
        'name' => $account->name,
        'label' => $account->code . ' — ' . $account->name,
    ])->values()->all();

    $quickAccounts = ($activeAccounts ?? collect())->take(10)->map(fn ($row) => [
        'id' => $row['account']->id,
        'code' => $row['account']->code,
        'name' => $row['account']->name,
        'lines' => $row['line_count'],
    ])->values()->all();
@endphp

<article
    class="gl-ledger"
    x-data="ledgerDetail(@js([
        'selectedId' => optional($selectedAccount)->id,
        'accounts' => $accountsForPicker,
        'action' => $glAction ?? route('admin.reports.general-ledger'),
        'periodParams' => $periodParams,
    ]))"
    @keydown.escape.window="pickerOpen = false"
>
    @if($selectedAccount)
        <div class="gl-ledger__hero print:hidden">
            <div class="gl-ledger__hero-glow" aria-hidden="true"></div>

            <div class="gl-ledger__hero-top">
                <div class="gl-ledger__identity">
                    <span class="gl-ledger__code">{{ $selectedAccount->code }}</span>
                    <div class="gl-ledger__identity-copy">
                        <h2 class="gl-ledger__name">{{ $selectedAccount->name }}</h2>
                        @if($breadcrumb)
                            <p class="gl-ledger__crumb">{{ $breadcrumb }}</p>
                        @endif
                        <p class="gl-ledger__meta-line">{{ $periodLabel }} · {{ $ccy }} · {{ $lines->count() }} posting{{ $lines->count() === 1 ? '' : 's' }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.reports.trial-balance', $periodParams) }}" class="gl-ledger__ghost-link">Trial balance</a>
            </div>

            <div class="gl-ledger__bridge">
                <div class="gl-ledger__bridge-card">
                    <span class="gl-ledger__bridge-label">Opening</span>
                    <span class="gl-ledger__bridge-value">{{ number_format($openingBalance, 2) }}</span>
                    <span class="gl-ledger__bridge-hint">Before {{ $from->format('d M Y') }}</span>
                </div>

                <div class="gl-ledger__bridge-flow" aria-hidden="true">
                    <span class="gl-ledger__bridge-arrow"></span>
                    <div class="gl-ledger__bridge-pills">
                        <span class="gl-ledger__pill gl-ledger__pill--dr">Dr {{ number_format($periodDebit, 2) }}</span>
                        <span class="gl-ledger__pill gl-ledger__pill--cr">Cr {{ number_format($periodCredit, 2) }}</span>
                    </div>
                    <span class="gl-ledger__bridge-arrow"></span>
                </div>

                <div class="gl-ledger__bridge-card gl-ledger__bridge-card--closing">
                    <span class="gl-ledger__bridge-label">Closing</span>
                    <span class="gl-ledger__bridge-value">{{ number_format($closingBalance, 2) }}</span>
                    <span class="gl-ledger__bridge-hint gl-ledger__bridge-hint--{{ $movementTone }}">
                        Net {{ $netMovement >= 0 ? '+' : '' }}{{ number_format($netMovement, 2) }}
                    </span>
                </div>
            </div>

            <div class="gl-ledger__picker">
                <form
                    x-ref="accountForm"
                    method="GET"
                    action="{{ $glAction ?? route('admin.reports.general-ledger') }}"
                    class="gl-ledger__picker-form"
                >
                    @foreach($periodParams as $key => $value)
                        @if($value !== null && $value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="account_id" value="{{ $selectedAccount->id }}">
                </form>

                <div class="gl-ledger__search-wrap">
                    <label class="sr-only" for="gl-ledger-search">Search accounts</label>
                    <input
                        id="gl-ledger-search"
                        type="search"
                        class="gl-ledger__search"
                        placeholder="Switch account — search by code or name"
                        x-model="query"
                        @focus="pickerOpen = true"
                        @click="pickerOpen = true"
                        autocomplete="off"
                    >
                    <div
                        class="gl-ledger__search-panel"
                        x-show="pickerOpen"
                        x-cloak
                        x-transition
                        @click.outside="pickerOpen = false"
                    >
                        <template x-if="filteredAccounts.length === 0">
                            <p class="gl-ledger__search-empty">No accounts match your search.</p>
                        </template>
                        <ul class="gl-ledger__search-list">
                            <template x-for="account in filteredAccounts" :key="account.id">
                                <li>
                                    <button
                                        type="button"
                                        class="gl-ledger__search-item"
                                        :class="Number(account.id) === Number(selectedId) ? 'is-active' : ''"
                                        @click="pickAccount(account.id)"
                                    >
                                        <span class="gl-ledger__search-code" x-text="account.code"></span>
                                        <span class="gl-ledger__search-name" x-text="account.name"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                @if($activeCount > 1)
                    <div class="gl-ledger__quick">
                        @foreach($quickAccounts as $quick)
                            <a
                                href="{{ route('admin.reports.general-ledger', array_merge($periodParams, ['account_id' => $quick['id']])) }}"
                                class="gl-ledger__quick-btn {{ optional($selectedAccount)->id === $quick['id'] ? 'is-active' : '' }}"
                            >
                                <span class="gl-ledger__quick-code">{{ $quick['code'] }}</span>
                                <span class="gl-ledger__quick-lines">{{ $quick['lines'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="gl-ledger__print-meta print:block">
            <p class="gl-ledger__print-title">General ledger — {{ $selectedAccount->code }} {{ $selectedAccount->name }}</p>
            <p class="gl-ledger__print-period">{{ $periodLabel }}</p>
        </div>

        <section class="gl-ledger__entries">
            <header class="gl-ledger__entries-head print:hidden">
                <div>
                    <h3 class="gl-ledger__entries-title">Journal entries</h3>
                    <p class="gl-ledger__entries-sub">{{ $lines->count() }} posting{{ $lines->count() === 1 ? '' : 's' }} in period</p>
                </div>
                <label class="gl-ledger__filter">
                    <span class="sr-only">Filter lines</span>
                    <input
                        type="search"
                        class="gl-ledger__filter-input"
                        placeholder="Filter by description or journal #"
                        x-model="lineQuery"
                    >
                </label>
            </header>

            <div class="gl-ledger__entries-table">
                <div class="gl-ledger__head-row">
                    <div class="gl-ledger__head-copy">
                        <span>Date</span>
                        <span>Journal</span>
                        <span>Description</span>
                    </div>
                    <div class="gl-ledger__head-figures">
                        <span>Debit</span>
                        <span>Credit</span>
                        <span>Balance</span>
                    </div>
                </div>

                <div class="gl-ledger__body">
                    <div class="gl-ledger__row gl-ledger__row--bookend">
                        <div class="gl-ledger__row-copy gl-ledger__row-copy--bookend">
                            <span class="gl-ledger__entry-tag">Start</span>
                            <div class="gl-ledger__bookend-copy">
                                <p class="gl-ledger__entry-title">Opening balance</p>
                                <p class="gl-ledger__entry-sub">Carried forward before {{ $from->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="gl-ledger__figures">
                            <span class="gl-ledger__amt"><span class="is-dash">—</span></span>
                            <span class="gl-ledger__amt"><span class="is-dash">—</span></span>
                            <span class="gl-ledger__amt gl-ledger__amt--strong">{{ number_format($openingBalance, 2) }}</span>
                        </div>
                    </div>

                    @forelse($lines as $line)
                        @php
                            $journalNumber = $line['journal']?->number ?? '';
                            $searchText = strtolower(trim(($line['description'] ?? '') . ' ' . $journalNumber));
                        @endphp
                        <div
                            class="gl-ledger__row"
                            data-search="{{ e($searchText) }}"
                            x-show="lineVisible($el.dataset.search)"
                        >
                            <div class="gl-ledger__row-copy">
                                <time class="gl-ledger__entry-date">
                                    {{ $line['date'] instanceof \Illuminate\Support\Carbon ? $line['date']->format('d M Y') : $line['date'] }}
                                </time>
                                <div class="gl-ledger__cell-journal">
                                    @if($line['journal'])
                                        <a href="{{ route('admin.journals.show', $line['journal']) }}" class="gl-ledger__je">{{ $line['journal']->number }}</a>
                                    @else
                                        <span class="is-dash">—</span>
                                    @endif
                                </div>
                                <p class="gl-ledger__entry-title">{{ $line['description'] ?? '—' }}</p>
                            </div>
                            <div class="gl-ledger__figures">
                                <span class="gl-ledger__amt">
                                    @if($line['debit'] > 0)
                                        <span class="is-dr">{{ number_format($line['debit'], 2) }}</span>
                                    @else
                                        <span class="is-dash">—</span>
                                    @endif
                                </span>
                                <span class="gl-ledger__amt">
                                    @if($line['credit'] > 0)
                                        <span class="is-cr">{{ number_format($line['credit'], 2) }}</span>
                                    @else
                                        <span class="is-dash">—</span>
                                    @endif
                                </span>
                                <span class="gl-ledger__amt gl-ledger__amt--strong">{{ number_format($line['balance'], 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="gl-ledger__empty-row">
                            <x-admin.empty-state
                                title="No postings in this period"
                                description="Widen the date range with Last month or Last quarter — opening balance is still shown above."
                            />
                        </div>
                    @endforelse

                    @if($lines->isNotEmpty())
                        <div class="gl-ledger__row gl-ledger__row--bookend gl-ledger__row--close">
                            <div class="gl-ledger__row-copy gl-ledger__row-copy--bookend">
                                <span class="gl-ledger__entry-tag">End</span>
                                <div class="gl-ledger__bookend-copy">
                                    <p class="gl-ledger__entry-title">Period total</p>
                                    <p class="gl-ledger__entry-sub">Movement through {{ $to->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="gl-ledger__figures">
                                <span class="gl-ledger__amt gl-ledger__amt--strong"><span class="is-dr">{{ number_format($periodDebit, 2) }}</span></span>
                                <span class="gl-ledger__amt gl-ledger__amt--strong"><span class="is-cr">{{ number_format($periodCredit, 2) }}</span></span>
                                <span class="gl-ledger__amt gl-ledger__amt--strong is-accent">{{ number_format($closingBalance, 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @else
        <div class="gl-ledger__empty gl-ledger__empty--page">
            <x-admin.empty-state
                title="No ledger data yet"
                description="Post journals from invoices, expenses, or payroll — or run php artisan db:seed to load demo data."
            />
        </div>
    @endif
</article>
