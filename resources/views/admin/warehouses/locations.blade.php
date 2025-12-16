@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Warehouse locations</h1>
                <p>Define racks/shelves/bins per warehouse for slotting.</p>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.warehouse-locations.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="warehouse_id">Warehouse</label>
                <select id="warehouse_id" name="warehouse_id" required>
                    <option value="">Select</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="code">Location code</label>
                <input id="code" name="code" placeholder="e.g. R1-S2-B3" required>
            </div>
            <div class="full-width">
                <label for="description">Description</label>
                <input id="description" name="description" placeholder="Rack/Shelf/Bin details">
            </div>
            <div class="form-actions">
                <button type="submit">Add location</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Defined locations</h1>
                <p>All slotting locations grouped by warehouse.</p>
            </div>
        </header>

        @foreach($warehouses as $warehouse)
            <h3>{{ $warehouse->name }}</h3>
            @if($warehouse->locations->isNotEmpty())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouse->locations as $location)
                            <tr>
                                <td>{{ $location->code }}</td>
                                <td>{{ $location->description ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="panel-note">No locations defined yet.</p>
            @endif
        @endforeach
    </section>
</div>
@endsection

