@props([
    'node',
    'from',
    'to',
    'ancestors' => [],
    'currencyCode' => 'BDT',
])

@php
    $isGroup = !empty($node['is_group']);
    $glQuery = ['account_id' => $node['id'], 'from' => $from->toDateString(), 'to' => $to->toDateString()];
    $indent = 0.75 + (count($ancestors) * 1.25);
@endphp

<div
    class="tb-inv__block"
    @if(count($ancestors) > 0)
        x-show="allAncestorsExpanded(@js($ancestors))"
        x-cloak
    @endif
>
    @if($isGroup)
        <div class="tb-inv__row tb-inv__row--group" style="--tb-indent: {{ $indent }}rem">
            <button
                type="button"
                class="tb-inv__toggle"
                @click="toggle({{ $node['id'] }})"
                :aria-expanded="isExpanded({{ $node['id'] }})"
            >
                <span class="tb-inv__chevron" :class="isExpanded({{ $node['id'] }}) ? 'is-open' : ''">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
                <span class="tb-inv__code">{{ $node['code'] }}</span>
                <span class="tb-inv__label">{{ $node['name'] }}</span>
                <span class="tb-inv__tag">Group</span>
            </button>
            <div class="tb-inv__figures">
                <span class="tb-inv__amt">{{ number_format($node['opening_debit'], 2) }}</span>
                <span class="tb-inv__amt">{{ number_format($node['opening_credit'], 2) }}</span>
                <span class="tb-inv__amt">{{ number_format($node['period_debit'], 2) }}</span>
                <span class="tb-inv__amt">{{ number_format($node['period_credit'], 2) }}</span>
                <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($node['closing_debit'], 2) }}</span>
                <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($node['closing_credit'], 2) }}</span>
            </div>
        </div>

        @foreach($node['children'] ?? [] as $child)
            @include('admin.finance.partials.trial-balance-node', [
                'node' => $child,
                'from' => $from,
                'to' => $to,
                'ancestors' => [...$ancestors, $node['id']],
                'currencyCode' => $currencyCode,
            ])
        @endforeach
    @else
        <a
            href="{{ route('admin.reports.general-ledger', $glQuery) }}"
            class="tb-inv__row tb-inv__row--leaf group"
            style="--tb-indent: {{ $indent }}rem"
        >
            <span class="tb-inv__leaf-copy">
                <span class="tb-inv__code">{{ $node['code'] }}</span>
                <span class="tb-inv__label">{{ $node['name'] }}</span>
            </span>
            <div class="tb-inv__figures">
                <span class="tb-inv__amt">{{ number_format($node['opening_debit'], 2) }}</span>
                <span class="tb-inv__amt">{{ number_format($node['opening_credit'], 2) }}</span>
                <span class="tb-inv__amt">{{ number_format($node['period_debit'], 2) }}</span>
                <span class="tb-inv__amt">{{ number_format($node['period_credit'], 2) }}</span>
                <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($node['closing_debit'], 2) }}</span>
                <span class="tb-inv__amt tb-inv__amt--strong">{{ number_format($node['closing_credit'], 2) }}</span>
            </div>
        </a>
    @endif
</div>
