@extends('layouts.app')

@section('content')
<div class="erp-page space-y-6">
    <x-admin.page-header icon="ledger" title="Manufacturing account schedule" :subtitle="$from->format('d M Y') . ' – ' . $to->format('d M Y')" />

    <x-admin.table-card title="{{ $schedule['name'] ?? 'Manufacturing Account' }}">
        <table class="erp-table">
            <tbody>
                @foreach($schedule['rows'] ?? [] as $row)
                    <tr class="{{ !empty($row['is_subtotal']) ? 'font-semibold' : '' }}">
                        <td style="padding-left: {{ 1 + (($row['level'] ?? 0) * 1.25) }}rem">{{ $row['name'] }}</td>
                        <td class="is-right">{{ number_format($row['amount'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td>Total manufacturing cost</td>
                    <td class="is-right">{{ number_format($schedule['total'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </x-admin.table-card>
</div>
@endsection
