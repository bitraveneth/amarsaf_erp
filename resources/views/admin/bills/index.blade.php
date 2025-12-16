@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Purchase bills</h1>
                <p>Track supplier bills and payments.</p>
            </div>
            <a href="{{ route('admin.bills.create') }}" class="button-primary">Add bill</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($bills->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bill #</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Net total</th>
                        <th>Status</th>
                        <th>Paid</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bills as $bill)
                        @php
                            $paid = $bill->payments->sum('amount');
                        @endphp
                        <tr>
                            <td>{{ $bill->number }}</td>
                            <td>{{ $bill->supplier->name }}</td>
                            <td>{{ $bill->bill_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($bill->net_total + $bill->vat_amount, 2) }}</td>
                            <td>{{ ucfirst($bill->status) }}</td>
                            <td>{{ number_format($paid, 2) }}</td>
                            <td>
                                @if($bill->status !== 'paid')
                                    <form action="{{ route('admin.bills.pay', $bill) }}" method="POST">
                                        @csrf
                                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="Amount" required>
                                        <button type="submit" class="button-secondary">Pay</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $bills->links() }}
        @else
            <p class="panel-note">No purchase bills recorded yet.</p>
        @endif
    </section>
</div>
@endsection

