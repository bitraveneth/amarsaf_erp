@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <h1>Tax classes</h1>
            <p>Define HSN/SAC and local tax codes with rates.</p>
        </header>
        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.tax-classes.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="name">Tax class name</label>
                <input id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="hsn_code">HSN / SAC code</label>
                <input id="hsn_code" name="hsn_code" value="{{ old('hsn_code') }}">
            </div>
            <div>
                <label for="local_tax_code">Local tax code</label>
                <input id="local_tax_code" name="local_tax_code" value="{{ old('local_tax_code') }}">
            </div>
            <div>
                <label for="rate">Rate (%)</label>
                <input id="rate" name="rate" type="number" step="0.01" value="{{ old('rate', 0) }}" required>
                @error('rate') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-actions">
                <button type="submit">Save tax class</button>
            </div>
        </form>

        @if($taxClasses->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Rate</th>
                        <th>HSN/SAC</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($taxClasses as $class)
                        <tr>
                            <td>{{ $class->name }}</td>
                            <td>{{ $class->rate }}%</td>
                            <td>{{ $class->hsn_code ?? $class->local_tax_code ?? '—' }}</td>
                            <td>{{ $class->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.tax-classes.edit', $class) }}" class="button-secondary">Edit</a>
                                <form action="{{ route('admin.tax-classes.destroy', $class) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this tax class?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $taxClasses->links() }}
        @else
            <p class="panel-note">No tax classes defined yet.</p>
        @endif
    </section>
</div>
@endsection
