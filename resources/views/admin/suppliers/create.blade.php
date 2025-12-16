@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add supplier</h1>
                <p>Capture vendor contact and tax details.</p>
            </div>
            <a href="{{ route('admin.suppliers.index') }}" class="button-secondary">Back to suppliers</a>
        </header>

        <form action="{{ route('admin.suppliers.store') }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="section-header">
                    <h3>Supplier details</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label for="contact_person">Contact person</label>
                        <input id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div class="full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address">{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label for="tax_id">Tax ID</label>
                        <input id="tax_id" name="tax_id" value="{{ old('tax_id') }}">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Save supplier</button>
            </div>
        </form>
    </section>
</div>
@endsection

