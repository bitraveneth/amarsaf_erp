@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit tax class</h1>
                <p>Update rate and HSN/local codes.</p>
            </div>
            <a href="{{ route('admin.tax-classes.index') }}" class="button-secondary">Back to tax classes</a>
        </header>

        <form action="{{ route('admin.tax-classes.update', $taxClass) }}" method="POST" class="form-grid">
            @csrf
            @method('PATCH')
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $taxClass->name) }}" required>
            </div>
            <div>
                <label for="hsn_code">HSN/SAC code</label>
                <input id="hsn_code" name="hsn_code" value="{{ old('hsn_code', $taxClass->hsn_code) }}">
            </div>
            <div>
                <label for="local_tax_code">Local tax code</label>
                <input id="local_tax_code" name="local_tax_code" value="{{ old('local_tax_code', $taxClass->local_tax_code) }}">
            </div>
            <div>
                <label for="rate">Rate (%)</label>
                <input id="rate" name="rate" type="number" step="0.01" value="{{ old('rate', $taxClass->rate) }}" required>
            </div>
            <div class="form-actions">
                <button type="submit">Save tax class</button>
            </div>
        </form>
    </section>
</div>
@endsection

