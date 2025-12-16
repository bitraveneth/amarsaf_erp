@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Production runs</h1>
                <p>Record bottling runs and QC results.</p>
            </div>
            <a href="{{ route('admin.production.create') }}" class="button-primary">Log production</a>
        </header>

        @if(isset($byLineShift) && $byLineShift->isNotEmpty())
            <div class="metric-grid">
                @foreach($byLineShift as $key => $qty)
                    @php
                        [$line, $shift] = explode('|', $key);
                        $capacity = $lineCapacities[$key] ?? null;
                        $util = $capacity ? ($qty / $capacity) * 100 : null;
                    @endphp
                    <article class="metric-card">
                        <p class="metric-label">{{ $line }} · {{ $shift }}</p>
                        <p class="metric-value">{{ $qty }}</p>
                        @if($util)
                            <p class="metric-change">{{ number_format($util, 0) }}% of capacity</p>
                        @else
                            <p class="metric-change">Capacity not set</p>
                        @endif
                    </article>
                @endforeach
            </div>
            <p class="panel-note">Today: {{ $today }}</p>
        @endif

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($runs->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Line / Shift</th>
                        <th>Quantity</th>
                        <th>QC</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($runs as $run)
                        <tr>
                            <td>{{ $run->product->name }}</td>
                            <td>{{ $run->batch->batch_code }}</td>
                            <td>{{ $run->line ?? '—' }} / {{ $run->shift ?? '—' }}</td>
                            <td>{{ $run->quantity }}</td>
                            <td>{{ ucfirst($run->qc_status) }}</td>
                            <td>
                                <a href="{{ route('admin.production.edit', $run) }}" class="button-secondary">Edit</a>
                                <form action="{{ route('admin.production.destroy', $run) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this production run?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $runs->links() }}
        @else
            <p class="panel-note">No production runs recorded yet.</p>
        @endif
    </section>
</div>
@endsection
