@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        title="Ledger gaps"
        subtitle="Documents saved in the business that never reached the ledger. Open a row to adjust it."
    >
        @if(collect($sections)->contains(fn ($section) => $section['can_sync']))
            <x-slot:actions>
                <form method="POST" action="{{ route('admin.expenses.sync-ledger') }}">
                    @csrf
                    <input type="hidden" name="scope" value="missing">
                    <input type="hidden" name="redirect" value="{{ route('admin.ledger-gaps.index') }}">
                    <button type="submit" class="erp-btn-primary">Sync missing expenses</button>
                </form>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis
        :show-header="false"
        title="Ledger gaps"
        :cards="[
            [
                'label' => 'Missing documents',
                'numeric' => number_format($missingCount),
                'caption' => 'Not on a posted voucher',
                'href' => route('admin.ledger-gaps.index'),
                'tone' => $missingCount > 0 ? 'orange' : 'success',
                'valueTone' => $missingCount > 0 ? 'warning' : 'neutral',
                'icon' => 'returns',
            ],
            [
                'label' => 'Off the ledger',
                'numeric' => number_format($gapAmount, 2),
                'currency' => $currency,
                'caption' => 'Amount with no posted voucher',
                'href' => route('admin.ledger-gaps.index'),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'receivables',
            ],
        ]"
    />

    @forelse($sections as $section)
        @continue($section['count'] === 0)
        <div class="erp-table-card">
            <div class="erp-table-card-header flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="erp-h3">{{ $section['title'] }}</h2>
                    <p class="erp-caption">{{ $section['description'] }} {{ number_format($section['count']) }} missing.</p>
                </div>
                <a href="{{ $section['list_url'] }}" class="erp-btn-secondary !px-3 !py-1.5 !text-sm">Open list</a>
            </div>
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Detail</th>
                            <th>Why</th>
                            <th class="is-right">Amount</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['rows'] as $row)
                            <tr>
                                <td><a href="{{ $row['href'] }}" class="erp-table-link">{{ $row['label'] }}</a></td>
                                <td class="erp-table-cell">{{ $row['meta'] }}</td>
                                <td><span class="erp-badge-warning">{{ $row['reason'] }}</span></td>
                                <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($row['amount'], 2) }}</span></td>
                                <td class="is-right whitespace-nowrap">
                                    <a href="{{ $row['href'] }}" class="erp-btn-action">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($section['count'] > count($section['rows']))
                <p class="erp-caption px-5 py-3">Showing {{ count($section['rows']) }} of {{ number_format($section['count']) }}. Open the list for the rest.</p>
            @endif
        </div>
    @empty
        <x-admin.empty-state title="Nothing is missing from the ledger" description="Expenses, bills, invoices, payroll, fleet costs, and logistics bills all have a posted voucher." />
    @endforelse

    @if($missingCount === 0 && $sections !== [])
        <x-admin.empty-state title="Nothing is missing from the ledger" description="Expenses, bills, invoices, payroll, fleet costs, and logistics bills all have a posted voucher." />
    @endif
</div>
@endsection
