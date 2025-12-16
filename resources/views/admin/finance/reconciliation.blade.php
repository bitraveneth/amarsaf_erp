@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Bank reconciliation</h1>
                <p>Mark customer receipts as matched to bank statements for {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}.</p>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($receipts->isEmpty())
            <p class="panel-note">No receipts in this period.</p>
        @else
            <form action="{{ route('admin.finance.reconciliation.update') }}" method="POST">
                @csrf
                <table class="data-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipts as $receipt)
                            <tr>
                                <td>
                                    @if(!$receipt->reconciled)
                                        <input type="checkbox" name="reconciled[]" value="{{ $receipt->id }}">
                                    @endif
                                </td>
                                <td>{{ $receipt->received_at->format('Y-m-d') }}</td>
                                <td>{{ $receipt->invoice?->number ?? '—' }}</td>
                                <td>{{ number_format($receipt->amount, 2) }}</td>
                                <td>{{ $receipt->payment_method ?? '—' }}</td>
                                <td>{{ $receipt->reconciled ? 'Reconciled' : 'Pending' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="form-actions">
                    <button type="submit">Mark selected as reconciled</button>
                </div>
            </form>
        @endif
    </section>
</div>
@endsection

