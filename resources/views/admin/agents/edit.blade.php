@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit agent</h1>
                <p>Update contact, area, and banking details.</p>
            </div>
            <a href="{{ route('admin.agents.index') }}" class="button-secondary">Back to agents</a>
        </header>

        <form action="{{ route('admin.agents.update', $agent) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            <div class="form-section">
                <div class="section-header">
                    <h3>Contact &amp; zone</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name', $agent->name) }}" required>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $agent->email) }}">
                    </div>
                    <div>
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone', $agent->phone) }}">
                    </div>
                    <div>
                        <label for="area">Area</label>
                        <input id="area" name="area" value="{{ old('area', $agent->area) }}">
                    </div>
                    <div>
                        <label for="zone">Zone</label>
                        <input id="zone" name="zone" value="{{ old('zone', $agent->zone) }}">
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
                        <input id="credit_limit" name="credit_limit" type="number" step="0.01" value="{{ old('credit_limit', $agent->credit_limit) }}">
                    </div>
                    <div>
                        <label for="parent_id">Parent agent</label>
                        <select id="parent_id" name="parent_id">
                            <option value="">None</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"{{ old('parent_id', $agent->parent_id) == $parent->id ? ' selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="full-width">
                        <label for="bank_details">Bank details</label>
                        <textarea id="bank_details" name="bank_details">{{ old('bank_details', $agent->bank_details) }}</textarea>
                    </div>
                    <div class="full-width">
                        <label for="kyc_documents">KYC docs (comma-separated)</label>
                        @php
                            $kyc = is_array($agent->kyc_documents) ? implode(', ', $agent->kyc_documents) : ($agent->kyc_documents ?? '');
                        @endphp
                        <input id="kyc_documents" name="kyc_documents[]" value="{{ old('kyc_documents.0', $kyc) }}">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection

