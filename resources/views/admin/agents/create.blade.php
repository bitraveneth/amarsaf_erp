@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add agent</h1>
                <p>Enter contact, area, and banking details.</p>
            </div>
            <a href="{{ route('admin.agents.index') }}" class="button-secondary">Back to agents</a>
        </header>

        <form action="{{ route('admin.agents.store') }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="section-header">
                    <h3>Contact & zone</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div>
                        <label for="area">Area</label>
                        <input id="area" name="area" value="{{ old('area') }}">
                    </div>
                    <div>
                        <label for="zone">Zone</label>
                        <input id="zone" name="zone" value="{{ old('zone') }}">
                    </div>
                </div>
            </div>
            <div class="form-section">
                <div class="section-header">
                    <h3>Financial/KYC</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="credit_limit">Credit limit</label>
                        <input id="credit_limit" name="credit_limit" type="number" step="0.01" value="{{ old('credit_limit') }}">
                    </div>
                    <div>
                        <label for="parent_id">Parent agent</label>
                        <select id="parent_id" name="parent_id">
                            <option value="">None</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"{{ old('parent_id') == $parent->id ? ' selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="full-width">
                        <label for="bank_details">Bank details</label>
                        <textarea id="bank_details" name="bank_details">{{ old('bank_details') }}</textarea>
                    </div>
                    <div class="full-width">
                        <label for="kyc_documents">KYC docs (comma-separated)</label>
                        <input id="kyc_documents" name="kyc_documents[]" value="{{ old('kyc_documents.0') }}">
                        <small class="text-muted">Add multiple documents by re-submitting the input (feature TBD).</small>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Save agent</button>
            </div>
        </form>
    </section>
</div>
@endsection
