@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        title="Accounting activity"
        subtitle="Vouchers posted, drafted, or reversed in the selected period."
    >
        <x-slot:actions>
            <x-dashboard.period-filter
                variant="compact"
                :action="route('admin.accounting-activity.index')"
                :range="$range"
                :from="$from->toDateString()"
                :to="$to->toDateString()"
                :range-options="$rangeOptions"
                :period-label="$periodLabel"
            />
            <form method="GET" action="{{ route('admin.accounting-activity.index') }}">
                <input type="hidden" name="range" value="{{ $range }}">
                @if($range === 'custom')
                    <input type="hidden" name="from" value="{{ $from->toDateString() }}">
                    <input type="hidden" name="to" value="{{ $to->toDateString() }}">
                @endif
                <select name="type" class="erp-select erp-select--sm" aria-label="Voucher type" onchange="this.form.submit()">
                    <option value="">All voucher types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:actions>
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis
        :show-header="false"
        title="Accounting activity"
        :cards="[
            [
                'label' => 'Events',
                'numeric' => number_format($summary['events']),
                'caption' => $periodLabel,
                'href' => route('admin.accounting-activity.index', request()->query()),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'orders',
            ],
            [
                'label' => 'Posted',
                'numeric' => number_format($summary['posted']),
                'caption' => 'On the ledger',
                'href' => route('admin.accounting-activity.index', request()->query()),
                'tone' => 'success',
                'valueTone' => 'neutral',
                'icon' => 'revenue',
            ],
            [
                'label' => 'Drafts',
                'numeric' => number_format($summary['drafts']),
                'caption' => 'Not posted yet',
                'href' => route('admin.accounting-activity.index', request()->query()),
                'tone' => 'orange',
                'valueTone' => 'warning',
                'icon' => 'returns',
            ],
        ]"
    />

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <h2 class="erp-h3">Activity</h2>
            <p class="erp-caption">Each voucher shows the ledgers it hit. Open the number to see the full journal.</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>What</th>
                        <th>Source</th>
                        <th>Description</th>
                        <th>Ledgers</th>
                        <th class="is-right">Amount</th>
                        <th>By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr class="cursor-pointer" onclick="if (!event.target.closest('a,button')) window.location = @js(route('admin.journals.show', $entry))">
                            <td class="erp-table-cell whitespace-nowrap">{{ $entry->entry_date?->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.journals.show', $entry) }}" class="erp-table-link">{{ $entry->number ?: 'Journal '.$entry->id }}</a>
                                <p class="erp-caption">{{ ucfirst(str_replace('_', ' ', (string) $entry->journal_type)) }}</p>
                            </td>
                            <td class="erp-table-cell">{{ $sourceLabels[$entry->source_type] ?? ($entry->source_type ? class_basename($entry->source_type) : 'Manual journal') }}</td>
                            <td class="erp-table-cell">{{ $entry->description ?: '—' }}</td>
                            <td>
                                @forelse($entry->lines as $line)
                                    @php
                                        $isDebit = (float) $line->debit > 0;
                                        $side = $isDebit ? 'Debit' : 'Credit';
                                        $lineAmount = $isDebit ? $line->debit : $line->credit;
                                    @endphp
                                    <p class="erp-table-cell leading-5">
                                        <span class="erp-caption">{{ $side }}</span>
                                        @if($line->account)
                                            <a href="{{ route('admin.accounts.show', $line->account) }}" class="erp-table-link">{{ $line->account->code }} {{ $line->account->name }}</a>
                                        @else
                                            <span>Ledger removed</span>
                                        @endif
                                        <span class="erp-table-num">{{ $currency }} {{ number_format((float) $lineAmount, 2) }}</span>
                                    </p>
                                @empty
                                    <span class="erp-caption">No ledger lines</span>
                                @endforelse
                            </td>
                            <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($entry->totalDebit(), 2) }}</span></td>
                            <td class="erp-table-cell">{{ $entry->postedByUser?->name ?: '—' }}</td>
                            <td>
                                @if($entry->status === 'posted')
                                    <span class="erp-badge-success">Posted</span>
                                @elseif($entry->status === 'draft')
                                    <span class="erp-badge-warning">Draft</span>
                                @else
                                    <span class="erp-badge-neutral">{{ ucfirst($entry->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin.empty-state title="No accounting activity in this period" description="Change the period or voucher type." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($entries->hasPages())
        <div>{{ $entries->links() }}</div>
    @endif
</div>
@endsection
