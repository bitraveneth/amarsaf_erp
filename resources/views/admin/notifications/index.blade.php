@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Notifications</h1>
                <p>System alerts that may need attention across SAFERP.</p>
            </div>
        </header>

        @if(empty($alerts))
            <p class="panel-note">All clear. There are no current notifications.</p>
        @else
            <ul class="activity-list">
                @foreach($alerts as $alert)
                    <li>{{ $alert }}</li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection

