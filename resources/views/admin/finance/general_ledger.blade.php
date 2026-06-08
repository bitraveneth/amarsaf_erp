@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="ledger"
        title="General ledger"
        :subtitle="$from->format('d M Y') . ' – ' . $to->format('d M Y')"
    >
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.finance.partials.export_center_button', ['module' => 'general-ledger', 'from' => $from, 'to' => $to])
                @include('admin.finance.partials.print_button')
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-panel method="GET">
        <div class="erp-field md:col-span-2">
            <label class="erp-label" for="gl-account">Account</label>
            <select id="gl-account" name="account_id" class="erp-select" required>
                <option value="">Select account</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" @selected(optional($selectedAccount)->id === $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="erp-field">
            <label class="erp-label" for="gl-from">From</label>
            <input id="gl-from" type="date" name="from" value="{{ $from->toDateString() }}" class="erp-input">
        </div>
        <div class="erp-field">
            <label class="erp-label" for="gl-to">To</label>
            <input id="gl-to" type="date" name="to" value="{{ $to->toDateString() }}" class="erp-input">
        </div>
        <button type="submit" class="erp-btn-primary">View ledger</button>
    </x-admin.filter-panel>

    @if($selectedAccount)
        <x-admin.table-card :title="$selectedAccount->code . ' — ' . $selectedAccount->name">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Journal</th>
                        <th>Description</th>
                        <th class="is-right">Debit</th>
                        <th class="is-right">Credit</th>
                        <th class="is-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lines as $line)
                        <tr>
                            <td>{{ $line['date'] instanceof \Illuminate\Support\Carbon ? $line['date']->format('d M Y') : $line['date'] }}</td>
                            <td>
                                @if($line['journal'])
                                    <a href="{{ route('admin.journals.show', $line['journal']) }}" class="erp-link">{{ $line['journal']->number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $line['description'] ?? '—' }}</td>
                            <td class="is-right">{{ number_format($line['debit'], 2) }}</td>
                            <td class="is-right">{{ number_format($line['credit'], 2) }}</td>
                            <td class="is-right font-medium">{{ number_format($line['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state title="No ledger activity" description="No posted journal lines for this account in the selected period." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.table-card>
    @else
        <x-admin.empty-state title="Select an account" description="Choose a chart-of-accounts entry and date range to view ledger activity." />
    @endif
</div>
@endsection
