@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="ledger"
        title="Trial balance"
        :subtitle="$from->format('d M Y') . ' – ' . $to->format('d M Y')"
    >
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.finance.partials.export_center_button', ['module' => 'trial-balance', 'from' => $from, 'to' => $to])
                @include('admin.finance.partials.print_button')
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-panel method="GET">
        <div class="erp-field">
            <label class="erp-label" for="tb-from">From</label>
            <input id="tb-from" type="date" name="from" value="{{ $from->toDateString() }}" class="erp-input">
        </div>
        <div class="erp-field">
            <label class="erp-label" for="tb-to">To</label>
            <input id="tb-to" type="date" name="to" value="{{ $to->toDateString() }}" class="erp-input">
        </div>
        <button type="submit" class="erp-btn-primary">Apply</button>
    </x-admin.filter-panel>

    <x-admin.table-card title="Account balances">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th class="is-right">Opening DR</th>
                    <th class="is-right">Opening CR</th>
                    <th class="is-right">Period DR</th>
                    <th class="is-right">Period CR</th>
                    <th class="is-right">Closing DR</th>
                    <th class="is-right">Closing CR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr class="{{ !empty($row['is_subtotal']) ? 'font-semibold bg-gray-50/80 dark:bg-gray-800/40' : '' }}">
                        <td style="padding-left: {{ 1 + (($row['level'] ?? 0) * 1.25) }}rem">
                            @if(empty($row['is_group']))
                                <a href="{{ route('admin.reports.general-ledger', ['account_id' => $row['id'], 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="erp-link">
                                    {{ $row['code'] }} — {{ $row['name'] }}
                                </a>
                            @else
                                {{ $row['code'] }} — {{ $row['name'] }}
                            @endif
                        </td>
                        <td class="is-right">{{ number_format($row['opening_debit'], 2) }}</td>
                        <td class="is-right">{{ number_format($row['opening_credit'], 2) }}</td>
                        <td class="is-right">{{ number_format($row['period_debit'], 2) }}</td>
                        <td class="is-right">{{ number_format($row['period_credit'], 2) }}</td>
                        <td class="is-right">{{ number_format($row['closing_debit'], 2) }}</td>
                        <td class="is-right">{{ number_format($row['closing_credit'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            @isset($totals)
                <tfoot class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                    <tr>
                        <td>Totals</td>
                        <td class="is-right">{{ number_format($totals['opening_debit'], 2) }}</td>
                        <td class="is-right">{{ number_format($totals['opening_credit'], 2) }}</td>
                        <td class="is-right">{{ number_format($totals['period_debit'], 2) }}</td>
                        <td class="is-right">{{ number_format($totals['period_credit'], 2) }}</td>
                        <td class="is-right">{{ number_format($totals['closing_debit'], 2) }}</td>
                        <td class="is-right">{{ number_format($totals['closing_credit'], 2) }}</td>
                    </tr>
                </tfoot>
            @endisset
        </table>
    </x-admin.table-card>
</div>
@endsection
