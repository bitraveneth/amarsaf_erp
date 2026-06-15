@php
    $statement = $incomeStatement ?? [];
    $rows = $statement['rows'] ?? [];
    $hasComparison = !empty($statement['hasComparison']);
    $plConfig = [
        'sectionKeys' => ['revenue', 'cogs', 'opex'],
        'groupCodes' => $statement['groupCodes'] ?? [],
        'defaultExpanded' => $statement['defaultExpandedGroups'] ?? [],
    ];
    $sectionShort = [
        'revenue' => 'Income',
        'cogs' => 'COGS',
        'opex' => 'OpEx',
    ];
@endphp

<article class="is-inv" x-data="plStatement(@js($plConfig))">
    <header class="is-inv__head print:hidden">
        <div class="is-inv__head-main">
            <h2 class="is-inv__title">Full breakdown</h2>
            <p class="is-inv__period">{{ $periodLabel ?? '' }} · {{ $currencyCode ?? 'BDT' }} · All income &amp; cost accounts</p>
            @if($hasComparison && !empty($statement['priorPeriodLabel']))
                <p class="is-inv__compare">Compared with {{ $statement['priorPeriodLabel'] }}</p>
            @endif
        </div>
        <div class="is-inv__head-actions">
            <form method="GET" action="{{ route('admin.reports.pl') }}" class="flex items-center">
                @foreach($queryParams ?? [] as $key => $value)
                    @if($key !== 'compare' && $value !== null && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <select name="compare" class="is-inv__compare-select" onchange="this.form.submit()">
                    <option value="prior" @selected(($compareMode ?? 'prior') === 'prior')>vs prior</option>
                    <option value="yoy" @selected(($compareMode ?? 'prior') === 'yoy')>vs last year</option>
                </select>
            </form>
        </div>
    </header>

    <div class="is-inv__sheet">
        <div class="is-inv__meta print:block">
            <p class="is-inv__meta-entity">Income statement</p>
            <p class="is-inv__meta-period">{{ $periodLabel ?? '' }}</p>
        </div>

        <div class="is-inv__lines {{ $hasComparison ? 'has-compare' : '' }}">
            <div class="is-inv__lines-head">
                <span class="is-inv__head-label">Line item</span>
                <div class="is-inv__figures is-inv__figures--head">
                    <span>Amount</span>
                    @if($hasComparison)
                        <span>Prior</span>
                    @endif
                    <span
                        class="is-inv__pct-head"
                        title="This line as a percentage of total income for the selected period"
                    >% of income</span>
                </div>
            </div>

            @foreach($rows as $row)
                @php
                    $type = $row['type'] ?? '';
                    $sectionKey = $row['section_key'] ?? '';
                    $groupCode = $row['group_code'] ?? '';
                    $milestoneKey = $row['milestone_key'] ?? '';
                    $isFinal = $milestoneKey === 'net';
                    $sectionBadge = $sectionShort[$sectionKey] ?? null;
                    $indent = match ($type) {
                        'group' => 1,
                        'account' => max(2, (int) ($row['indent'] ?? 2)),
                        default => 0,
                    };
                @endphp

                @if($type === 'section')
                    <div class="is-inv__lines-row is-inv__row--section is-inv__row--{{ $sectionKey }}">
                        <div class="is-inv__desc" style="--inv-indent: {{ $indent }}">
                            <button type="button"
                                    class="is-inv__toggle"
                                    @click="toggleSection(@js($sectionKey))"
                                    :aria-expanded="isSectionOpen(@js($sectionKey))">
                                <span class="is-inv__chevron" :class="isSectionOpen(@js($sectionKey)) ? 'is-open' : ''">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                                @if($sectionBadge)
                                    <span class="is-inv__tag is-inv__tag--{{ $sectionKey }}">{{ $sectionBadge }}</span>
                                @endif
                                <span class="is-inv__label">{{ $row['label'] ?? '' }}</span>
                            </button>
                        </div>
                        <div class="is-inv__figures">
                            <span class="is-inv__amt is-inv__amt--section {{ !empty($row['is_cost']) ? 'is-cost' : 'is-revenue' }}">{{ $row['amount_display'] ?? '' }}</span>
                            @if($hasComparison)
                                <span class="is-inv__amt is-muted">{{ $row['prior_display'] ?? '—' }}</span>
                            @endif
                            <span class="is-inv__pct is-muted">{{ $row['pct_display'] ?? '' }}</span>
                        </div>
                    </div>
                @elseif($type === 'group')
                    <div class="is-inv__lines-row is-inv__row--group"
                         x-show="isGroupRowVisible(@js($groupCode), @js($sectionKey))"
                         x-cloak>
                        <div class="is-inv__desc" style="--inv-indent: {{ $indent }}">
                            <button type="button"
                                    class="is-inv__toggle"
                                    @click="toggleGroup(@js($groupCode))"
                                    :aria-expanded="isGroupExpanded(@js($groupCode))">
                                <span class="is-inv__chevron is-inv__chevron--sm" :class="isGroupExpanded(@js($groupCode)) ? 'is-open' : ''">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                                @if(!empty($row['code']))
                                    <span class="is-inv__code">{{ $row['code'] }}</span>
                                @endif
                                <span class="is-inv__label">{{ $row['label'] ?? '' }}</span>
                            </button>
                        </div>
                        <div class="is-inv__figures">
                            <span class="is-inv__amt {{ !empty($row['is_cost']) ? 'is-cost' : '' }}">{{ $row['amount_display'] ?? '' }}</span>
                            @if($hasComparison)
                                <span class="is-inv__amt is-muted">{{ $row['prior_display'] ?? '—' }}</span>
                            @endif
                            <span class="is-inv__pct is-muted">{{ $row['pct_display'] ?? '' }}</span>
                        </div>
                    </div>
                    @elseif($type === 'account')
                        @php $isZeroLine = abs((float) ($row['amount'] ?? 0)) < 0.01; @endphp
                        <div class="is-inv__lines-row is-inv__row--account {{ !empty($row['is_subgroup']) ? 'is-subgroup' : '' }} {{ $isZeroLine ? 'is-zero' : '' }}"
                         x-show="isAccountRowVisible(@js($groupCode), @js($sectionKey))"
                         x-cloak>
                        <div class="is-inv__desc" style="--inv-indent: {{ $indent }}">
                            <div class="is-inv__line-label">
                                @if(!empty($row['code']))
                                    <span class="is-inv__code">{{ $row['code'] }}</span>
                                @endif
                                @if(!empty($row['gl_url']))
                                    <a href="{{ $row['gl_url'] }}" class="erp-link">{{ $row['label'] }}</a>
                                @else
                                    <span>{{ $row['label'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="is-inv__figures">
                            <span class="is-inv__amt {{ !empty($row['is_cost']) ? 'is-cost' : '' }}">{{ $row['amount_display'] ?? '' }}</span>
                            @if($hasComparison)
                                <span class="is-inv__amt is-muted">{{ $row['prior_display'] ?? '—' }}</span>
                            @endif
                            <span class="is-inv__pct is-muted">{{ $row['pct_display'] ?? '' }}</span>
                        </div>
                    </div>
                @elseif($type === 'milestone')
                    <div class="is-inv__lines-row is-inv__row--milestone is-{{ $row['tone'] ?? 'positive' }} {{ $isFinal ? 'is-final' : '' }}">
                        <div class="is-inv__desc" style="--inv-indent: 0">
                            <span class="is-inv__milestone-label">{{ $row['label'] ?? '' }}</span>
                        </div>
                        <div class="is-inv__figures">
                            <span class="is-inv__amt is-inv__amt--milestone">{{ $row['amount_display'] ?? '' }}</span>
                            @if($hasComparison)
                                <span class="is-inv__amt is-muted">{{ $row['prior_display'] ?? '—' }}</span>
                            @endif
                            <span class="is-inv__pct is-muted">{{ $row['pct_display'] ?? '' }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <footer class="is-inv__foot">
            @if(!empty($trust['last_journal_at']))
                <p>Last journal in period: {{ $trust['last_journal_at'] }}@if(!empty($trust['last_journal_ref'])) · {{ $trust['last_journal_ref'] }}@endif</p>
            @endif
            <p class="print:hidden">Every income and cost account from your chart of accounts is listed. Lines with no posted activity in this period show 0.00.</p>
        </footer>
    </div>
</article>
