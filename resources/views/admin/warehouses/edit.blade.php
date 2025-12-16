@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit warehouse</h1>
                <p>Update depot, godown, or consignment details.</p>
            </div>
            <a href="{{ route('admin.warehouses.index') }}" class="button-secondary">Back to list</a>
        </header>

        <form action="{{ route('admin.warehouses.update', $warehouse) }}" method="POST" class="form-grid">
            @csrf
            @method('PATCH')
            <div>
                <label for="name">Warehouse name</label>
                <input id="name" name="name" value="{{ old('name', $warehouse->name) }}" required>
            </div>
            <div>
                <label for="type">Type</label>
                <input id="type" name="type" value="{{ old('type', $warehouse->type) }}">
            </div>
            <div class="full-width">
                <label for="address">Address</label>
                <textarea id="address" name="address">{{ old('address', $warehouse->address) }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection

