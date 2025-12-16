@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>{{ $account->exists ? 'Edit account' : 'Add account' }}</h1>
                <p>Define GL account code, name, and type.</p>
            </div>
            <a href="{{ route('admin.accounts.index') }}" class="button-secondary">Back to accounts</a>
        </header>

        <form action="{{ $account->exists ? route('admin.accounts.update', $account) : route('admin.accounts.store') }}" method="POST" class="form-form">
            @csrf
            @if($account->exists)
                @method('PATCH')
            @endif
            <div class="form-section">
                <div class="section-header">
                    <h3>Account details</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="code">Code</label>
                        <input id="code" name="code" value="{{ old('code', $account->code) }}" required>
                    </div>
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name', $account->name) }}" required>
                    </div>
                    <div>
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            @foreach(['asset','liability','equity','income','expense'] as $type)
                                <option value="{{ $type }}"{{ old('type', $account->type) === $type ? ' selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="is_active">Active</label>
                        <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Save account</button>
            </div>
        </form>
    </section>
</div>
@endsection

