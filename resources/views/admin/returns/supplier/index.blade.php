@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Supplier returns</h1>
                <p>Stock returned to suppliers (recorded via write-off as “Return to supplier”).</p>
            </div>
            <a href="{{ route('admin.stock.writeoff') }}" class="button-secondary">Record supplier return</a>
        </header>

        @if($returns->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returns as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('Y-m-d') }}</td>
                            <td>{{ $movement->stockEntry->product->name }}</td>
                            <td>{{ $movement->stockEntry->warehouse->name }}</td>
                            <td>{{ number_format($movement->quantity, 2) }}</td>
                            <td>{{ $movement->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $returns->links() }}
        @else
            <p class="panel-note">No supplier returns recorded yet. Use the stock write-off screen and select “Return to supplier”.</p>
        @endif
    </section>
</div>
@endsection

