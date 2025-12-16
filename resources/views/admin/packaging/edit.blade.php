@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit packaging</h1>
                <p>Update packaging title, unit, or description.</p>
            </div>
            <a href="{{ route('admin.packaging.index') }}" class="button-secondary">Back to list</a>
        </header>

        <form action="{{ route('admin.packaging.update', $packagingType) }}" method="POST" class="form-grid">
            @csrf
            @method('PATCH')
            <div>
                <label for="name">Packaging name</label>
                <input id="name" name="name" value="{{ old('name', $packagingType->name) }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="unit">Unit label</label>
                <input id="unit" name="unit" value="{{ old('unit', $packagingType->unit) }}">
            </div>
            <div class="full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description">{{ old('description', $packagingType->description) }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection
