@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit badge</h1>
                <p>Update badge details.</p>
            </div>
            <a href="{{ route('admin.badges.index') }}" class="button-secondary">Back to badges</a>
        </header>

        <form action="{{ route('admin.badges.update', $badge) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            @include('admin.employees.badges.partials.form', ['badge' => $badge])
            <div class="form-actions">
                <button type="submit">Update badge</button>
            </div>
        </form>
    </section>
</div>
@endsection

