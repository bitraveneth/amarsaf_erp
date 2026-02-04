@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Marketing campaigns</h1>
                <p>Track social and offline campaigns, reach, and spend.</p>
            </div>
            <a href="{{ route('admin.campaigns.create') }}" class="button-primary">Add campaign</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($campaigns->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Platform</th>
                        <th>Period</th>
                        <th>Reach</th>
                        <th>Impressions</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>
                            <td>{{ ucfirst($campaign->platform) }}</td>
                            <td>
                                {{ $campaign->start_date?->format('Y-m-d') }}
                                –
                                {{ $campaign->end_date?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td>{{ number_format($campaign->reach ?? 0) }}</td>
                            <td>{{ number_format($campaign->impressions ?? 0) }}</td>
                            <td>{{ $campaign->cost !== null ? number_format($campaign->cost, 2) : '—' }}</td>
                            <td>{{ ucfirst($campaign->status) }}</td>
                            <td>
                                <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.campaigns.destroy', $campaign) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this campaign? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $campaigns->links() }}
        @else
            <p class="panel-note">No campaigns recorded yet.</p>
        @endif
    </section>
</div>
@endsection

