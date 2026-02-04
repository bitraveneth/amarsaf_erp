@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>{{ $agent->name }}</h1>
                <p>{{ $agent->area ?? 'Agent' }} @if($agent->zone) · Zone {{ $agent->zone }} @endif</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.agents.edit', $agent) }}" class="button-secondary">Edit agent</a>
                <a href="{{ route('admin.agents.index') }}" class="button-secondary">Back to agents</a>
            </div>
        </header>

        <div class="profile-grid">
            <div class="profile-main">
                <div class="form-section">
                    <div class="section-header">
                        <h3>Contact</h3>
                    </div>
                    <div class="form-grid">
                        <div>
                            <p class="metric-label">Email</p>
                            <p>{{ $agent->email ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="metric-label">Phone</p>
                            <p>{{ $agent->phone ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <h3>Location</h3>
                    </div>
                    <div class="form-grid">
                        <div>
                            <p class="metric-label">Area</p>
                            <p>{{ $agent->area ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="metric-label">Zone</p>
                            <p>{{ $agent->zone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="metric-label">Location code</p>
                            <p>{{ $agent->location_code ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="metric-label">Special code</p>
                            <p>{{ $agent->special_code ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-side">
                <div class="profile-card">
                    <h3>Account</h3>
                    <p><strong>Credit limit:</strong> {{ number_format($agent->credit_limit ?? 0, 2) }}</p>
                    <p><strong>Status:</strong> {{ $agent->is_active ? 'Active' : 'Suspended' }}</p>
                    <p><strong>Parent agent:</strong> {{ $agent->parent->name ?? '—' }}</p>
                    <a href="{{ route('admin.agents.ledger.show', $agent) }}" class="button-secondary">View ledger</a>
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h2>Quick links</h2>
                <p>Jump into detailed views for this agent.</p>
            </div>
        </header>
        <div class="button-group">
            <a href="{{ route('admin.agents.pricing.edit', $agent) }}" class="button-secondary">Pricing &amp; commission</a>
            <a href="{{ route('admin.agents.ledger.show', $agent) }}" class="button-secondary">Ledger</a>
            <a href="{{ route('admin.gifts.index', ['agent_id' => $agent->id]) }}" class="button-secondary">Gifts</a>
            <a href="{{ route('admin.orders.index', ['agent_id' => $agent->id]) }}" class="button-secondary">Orders (filter manually)</a>
        </div>
    </section>
</div>
@endsection

