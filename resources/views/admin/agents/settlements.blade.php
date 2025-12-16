@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Commission settlements</h1>
                <p>Monthly totals by agent for {{ $month->format('F Y') }}.</p>
            </div>
            <form action="{{ route('admin.settlements.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <button type="submit" class="button-secondary">Recalculate month</button>
            </form>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($settlements->isEmpty())
            <p class="panel-note">No settlements for this month yet.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Sales</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settlements as $settlement)
                        @php
                            $rate = $settlement->sales_total > 0 ? ($settlement->commission_total / $settlement->sales_total) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $settlement->agent->name }}</td>
                            <td>{{ number_format($settlement->sales_total, 2) }}</td>
                            <td>{{ number_format($settlement->commission_total, 2) }} ({{ number_format($rate, 2) }}%)</td>
                            <td>{{ ucfirst($settlement->status) }}</td>
                            <td>
                                <form action="{{ route('admin.settlements.update-status', $settlement) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status">
                                        @foreach(['open','approved','paid'] as $status)
                                            <option value="{{ $status }}"{{ $settlement->status === $status ? ' selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="button-secondary">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection

