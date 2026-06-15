@php
    $groupIds = collect($tree ?? [])
        ->flatMap(function ($node) {
            $walk = function (array $item) use (&$walk) {
                $ids = !empty($item['is_group']) ? [(int) $item['id']] : [];
                foreach ($item['children'] ?? [] as $child) {
                    $ids = array_merge($ids, $walk($child));
                }

                return $ids;
            };

            return $walk($node);
        })
        ->unique()
        ->values()
        ->all();
@endphp

<article
    class="tb-inv"
    x-data="trialBalance(@js(['groupIds' => $groupIds]))"
>
    <header class="tb-inv__head print:hidden">
        <div class="tb-inv__head-main">
            <h2 class="tb-inv__title">Account balances</h2>
            <p class="tb-inv__period">{{ $periodLabel }} · {{ $currencyCode }} · Click a group to expand · Leaf accounts open the ledger</p>
        </div>
        <div class="tb-inv__head-actions">
            <button type="button" class="erp-btn-secondary !px-3 !py-1.5 !text-xs" @click="expandAll()">Expand all</button>
            <button type="button" class="erp-btn-secondary !px-3 !py-1.5 !text-xs" @click="collapseAll()">Collapse all</button>
        </div>
    </header>

    <div class="tb-inv__sheet">
        <div class="tb-inv__meta print:block">
            <p class="tb-inv__meta-entity">Trial balance</p>
            <p class="tb-inv__meta-period">{{ $periodLabel }}</p>
        </div>

        <div class="tb-inv__table-wrap">
            <div class="tb-inv__head-row">
                <span class="tb-inv__head-account">Account</span>
                <div class="tb-inv__head-figures">
                    <span>Opening DR</span>
                    <span>Opening CR</span>
                    <span>Period DR</span>
                    <span>Period CR</span>
                    <span>Closing DR</span>
                    <span>Closing CR</span>
                </div>
            </div>

            <div class="tb-inv__body">
                @forelse($tree as $root)
                    @include('admin.finance.partials.trial-balance-node', [
                        'node' => $root,
                        'from' => $from,
                        'to' => $to,
                        'ancestors' => [],
                        'currencyCode' => $currencyCode,
                    ])
                @empty
                    <div class="tb-inv__empty">
                        <x-admin.empty-state title="No account balances" description="No posted activity found for this period." />
                    </div>
                @endforelse
            </div>

            @isset($totals)
                <div class="tb-inv__foot-row">
                    <span class="tb-inv__foot-label">Totals (leaf accounts)</span>
                    <div class="tb-inv__figures">
                        <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($totals['opening_debit'], 2) }}</span>
                        <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($totals['opening_credit'], 2) }}</span>
                        <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($totals['period_debit'], 2) }}</span>
                        <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($totals['period_credit'], 2) }}</span>
                        <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($totals['closing_debit'], 2) }}</span>
                        <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($totals['closing_credit'], 2) }}</span>
                    </div>
                </div>
            @endisset
        </div>
    </div>
</article>
