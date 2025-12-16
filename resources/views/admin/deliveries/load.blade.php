@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Vehicle load</h1>
                <p>Estimated crate load per vehicle for {{ $date->format('Y-m-d') }}.</p>
            </div>
        </header>

        @if(empty($rows))
            <p class="panel-note">No deliveries scheduled for this date.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Capacity (crates)</th>
                        <th>Planned load (crates)</th>
                        <th>Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $capacity = $row['vehicle']->capacity_crates ?? 0;
                            $load = $row['crateLoad'];
                            $util = $capacity > 0 ? ($load / $capacity) * 100 : null;
                        @endphp
                        <tr>
                            <td>{{ $row['vehicle']->name }}</td>
                            <td>{{ $capacity ?: '—' }}</td>
                            <td>{{ $load }}</td>
                            <td>{{ $util ? number_format($util, 0) . '%' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection

