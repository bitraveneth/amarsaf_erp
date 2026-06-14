@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Route cost report" subtitle="Logistics spend vs zone sales by delivery route.">
        <x-slot:actions>
            <a href="{{ route('admin.logistics.dashboard') }}" class="erp-btn-secondary">Dashboard</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="erp-filter-panel flex flex-wrap items-end gap-3">
        <div><label class="mb-1 block text-xs text-gray-500">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"></div>
        <div><label class="mb-1 block text-xs text-gray-500">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"></div>
        <button type="submit" class="erp-btn-secondary">Apply</button>
    </form>

    <div class="erp-table-card">
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>Route</th>
                        <th>Zone</th>
                        <th class="is-right">Fleet cost</th>
                        <th class="is-right">Carrier cost</th>
                        <th class="is-right">Total logistics</th>
                        <th class="is-right">Zone sales*</th>
                        <th class="is-right">After logistics</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row['route']->name }}</td>
                            <td>{{ $row['route']->zone ?: '—' }}</td>
                            <td class="is-right erp-table-num">BDT {{ number_format($row['fleet_cost'], 0) }}</td>
                            <td class="is-right erp-table-num">BDT {{ number_format($row['carrier_cost'], 0) }}</td>
                            <td class="is-right erp-table-num font-medium">BDT {{ number_format($row['logistics_cost'], 0) }}</td>
                            <td class="is-right erp-table-num">BDT {{ number_format($row['zone_revenue'], 0) }}</td>
                            <td class="is-right erp-table-num {{ $row['margin_after_logistics'] < 0 ? 'text-error-600' : 'text-success-600' }}">BDT {{ number_format($row['margin_after_logistics'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="px-6 py-3 text-xs text-gray-500">* Zone sales = order totals from agents in the same zone (approximate).</p>
    </div>
</div>
@endsection
