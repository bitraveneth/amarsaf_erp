@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Packaging master</h1>
                <p>Define bottles, crates, cartons and their labels.</p>
            </div>
            <a href="{{ route('admin.packaging.index') }}" class="button-secondary">Refresh</a>
        </header>
        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.packaging.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="name">Packaging name</label>
                <input id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="unit">Unit label</label>
                <input id="unit" name="unit" value="{{ old('unit') }}">
            </div>
            <div class="full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description">{{ old('description') }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Add packaging</button>
            </div>
        </form>

        @if($packagingTypes->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Unit</th>
                        <th>Description</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packagingTypes as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->unit ?? '—' }}</td>
                            <td>{{ $type->description ?? '—' }}</td>
                            <td>{{ $type->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.packaging.edit', $type) }}" class="button-secondary">Edit</a>
                                <form action="{{ route('admin.packaging.destroy', $type) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this packaging type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $packagingTypes->links() }}
        @else
            <p class="panel-note">No packaging types yet.</p>
        @endif
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Unit conversions</h1>
                <p>Define how many base units exist between packaging types.</p>
            </div>
        </header>

        <form action="{{ route('admin.packaging.conversions.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="from_packaging_type_id">From packaging</label>
                <select id="from_packaging_type_id" name="from_packaging_type_id" required>
                    <option value="">Select</option>
                    @foreach($packagingTypes as $type)
                        <option value="{{ $type->id }}"{{ old('from_packaging_type_id') == $type->id ? ' selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('from_packaging_type_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="to_packaging_type_id">To packaging</label>
                <select id="to_packaging_type_id" name="to_packaging_type_id" required>
                    <option value="">Select</option>
                    @foreach($packagingTypes as $type)
                        <option value="{{ $type->id }}"{{ old('to_packaging_type_id') == $type->id ? ' selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('to_packaging_type_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="factor">Conversion factor</label>
                <input id="factor" name="factor" type="number" min="0.0001" step="0.0001" value="{{ old('factor', 1) }}" required>
                @error('factor') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="notes">Notes</label>
                <input id="notes" name="notes" value="{{ old('notes') }}">
            </div>
            <div class="form-actions">
                <button type="submit">Save conversion</button>
            </div>
        </form>

        @if($conversions->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Factor</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conversions as $conversion)
                        <tr>
                            <td>{{ $conversion->fromType->name }}</td>
                            <td>{{ $conversion->toType->name }}</td>
                            <td>{{ number_format($conversion->factor, 4) }}</td>
                            <td>{{ $conversion->notes ?? '—' }}</td>
                            <td>
                                <form action="{{ route('admin.packaging.conversions.destroy', $conversion) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this conversion?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $conversions->withQueryString()->links() }}
        @else
            <p class="panel-note">No conversions defined yet.</p>
        @endif
    </section>
</div>
@endsection
