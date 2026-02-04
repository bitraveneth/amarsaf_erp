@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Commission rules</h1>
                <p>Commission rules are defined per agent. Open an agent’s pricing to set SKU‑wise or order‑wise commissions.</p>
            </div>
        </header>

        @if($agents->isEmpty())
            <p class="panel-note">No agents found. Create agents first, then configure commission rules from the pricing screen.</p>
        @else
            <table class="data-table">
                <thead>
                <tr>
                    <th>Agent</th>
                    <th>Area / Zone</th>
                    <th>Rules</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($agents as $agent)
                    <tr>
                        <td>{{ $agent->name }}</td>
                        <td>{{ $agent->area }} @if($agent->zone) · {{ $agent->zone }} @endif</td>
                        <td>
                            @if($agent->commissions->isEmpty())
                                <span class="text-muted">No rules</span>
                            @else
                                {{ $agent->commissions->count() }} rule(s)
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.agents.pricing.edit', $agent) }}" class="button-secondary">
                                Open pricing &amp; rules
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection

