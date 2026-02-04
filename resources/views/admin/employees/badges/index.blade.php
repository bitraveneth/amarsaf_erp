@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Badges</h1>
                <p>Manage recognition badges that can be granted to employees.</p>
            </div>
            <a href="{{ route('admin.badges.create') }}" class="button-primary">Add badge</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($badges->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Status</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($badges as $badge)
                        <tr>
                            <td>{{ $badge->name }}</td>
                            <td>{{ $badge->code }}</td>
                            <td>{{ $badge->description ?? '—' }}</td>
                            <td>
                                @if($badge->color)
                                    <span class="badge-pill" style="background: {{ $badge->color }};">&nbsp;</span>
                                    {{ $badge->color }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $badge->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <a href="{{ route('admin.badges.edit', $badge) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this badge? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.badges.grant-form', $badge) }}" class="button-secondary">
                                    Grant
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No badges defined yet.</p>
        @endif
    </section>
</div>
@endsection
