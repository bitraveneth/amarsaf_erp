@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add badge</h1>
                <p>Create a badge that can be granted to employees.</p>
            </div>
            <a href="{{ route('admin.badges.index') }}" class="button-secondary">Back to badges</a>
        </header>

        <form action="{{ route('admin.badges.store') }}" method="POST" class="form-form">
            @csrf
            @include('admin.employees.badges.partials.form', ['badge' => $badge])
            <div class="form-actions">
                <button type="submit">Save badge</button>
            </div>
        </form>
    </section>
</div>
@endsection

