@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Agent master</h1>
                <p>List, search, and manage your agent network.</p>
            </div>
            <a href="{{ route('admin.agents.create') }}" class="button-primary">Add agent</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($agents->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Zone</th>
                        <th>Parent</th>
                        <th>Credit limit</th>
                        <th>Status</th>
                        <th>Pricing</th>
                        <th>Ledger</th>
                        <th>Gifts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $agent)
                        <tr>
                            <td>{{ $agent->name }}</td>
                            <td>{{ $agent->zone ?? '—' }}</td>
                            <td>{{ $agent->parent->name ?? '—' }}</td>
                            <td>{{ number_format($agent->credit_limit, 2) }}</td>
                            <td>{{ $agent->is_active ? 'Active' : 'Suspended' }}</td>
                            <td>
                                <a href="{{ route('admin.agents.pricing.edit', $agent) }}" class="button-secondary">
                                    Pricing &amp; commission
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.agents.ledger.show', $agent) }}" class="button-secondary">
                                    Ledger
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.gifts.index', ['agent_id' => $agent->id]) }}" class="button-secondary">
                                    Gifts
                                </a>
                            </td>
                            <td>
                                <div class="button-group">
                                    <a href="{{ route('admin.agents.show', $agent) }}" class="button-secondary">
                                        View
                                    </a>
                                    <a href="{{ route('admin.agents.edit', $agent) }}" class="button-secondary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this agent? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button-secondary">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $agents->links() }}
        @else
            <p class="panel-note">No agents registered yet.</p>
        @endif
    </section>
</div>
@endsection
