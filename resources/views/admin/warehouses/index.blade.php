@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Warehouses</h1>
                <p>Track stock across depots and hubs.</p>
            </div>
            <a href="{{ route('admin.warehouses.create') }}" class="button-primary">Add warehouse</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <div class="master-grid">
            @foreach($warehouses as $warehouse)
                <article>
                    <p class="metric-label">{{ $warehouse->name }}</p>
                    <h3>{{ $warehouse->entries_count }}</h3>
                    <small>{{ $warehouse->type ?? 'Depot' }}</small>
                    <p class="panel-note">{{ Str::limit($warehouse->address ?? 'No address yet.', 80) }}</p>
                    <div class="button-group">
                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="button-secondary">Edit</a>
                        <form action="{{ route('admin.warehouses.clear-stock', $warehouse) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Clear all stock entries for this warehouse?');">
                            @csrf
                            <button type="submit" class="button-secondary">Clear stock</button>
                        </form>
                        <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this warehouse?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button-secondary">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <h2>Recent stock</h2>
        @if($entries->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Warehouse</th>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td>{{ $entry->warehouse->name }}</td>
                            <td>{{ $entry->product->name }}</td>
                            <td>{{ $entry->batch->batch_code ?? '—' }}</td>
                            <td>{{ number_format($entry->quantity, 2) }}</td>
                            <td>{{ ucfirst($entry->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No stock entries yet.</p>
        @endif
    </section>
</div>
@endsection
