@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Customer gifts</h1>
                <p>Track birthday, yearly, and bonus gifts for agents/dealers.</p>
            </div>
            <a href="{{ route('admin.gifts.create') }}" class="button-primary">Add gift</a>
        </header>

        <form method="GET" action="{{ route('admin.gifts.index') }}" class="inline-filters">
            <label>
                From
                <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}">
            </label>
            <label>
                To
                <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}">
            </label>
            <label>
                Agent
                <select name="agent_id">
                    <option value="">All</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ (string)request('agent_id') === (string)$agent->id ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="button-secondary">Filter</button>
        </form>

        <p class="panel-note">
            Total gift value in period: <strong>{{ number_format($total, 2) }}</strong>
        </p>

        @if($gifts->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Agent</th>
                        <th>Handled by</th>
                        <th>Occasion</th>
                        <th>Gift</th>
                        <th>Amount</th>
                        <th>Campaign</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gifts as $gift)
                        <tr>
                            <td>{{ $gift->date?->format('Y-m-d') }}</td>
                            <td>{{ $gift->agent->name }}</td>
                            <td>{{ $gift->employee?->name ?? '—' }}</td>
                            <td>{{ $gift->occasion ?? '—' }}</td>
                            <td>{{ $gift->gift_type ?? $gift->description ?? '—' }}</td>
                            <td>{{ $gift->amount !== null ? number_format($gift->amount, 2) : '—' }}</td>
                            <td>{{ $gift->campaign_code ?? '—' }}</td>
                            <td>{{ ucfirst($gift->status) }}</td>
                            <td>
                                <a href="{{ route('admin.gifts.edit', $gift) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.gifts.destroy', $gift) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this gift record? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $gifts->links() }}
        @else
            <p class="panel-note">No customer gifts recorded for this period.</p>
        @endif
    </section>
</div>
@endsection

