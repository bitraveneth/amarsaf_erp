@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add employee</h1>
                <p>Create a new employee profile.</p>
            </div>
            <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
        </header>

        <form action="{{ route('admin.employees.store') }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @include('admin.employees.partials.form', ['employee' => $employee])
            <div class="form-actions">
                <button type="submit">Save employee</button>
            </div>
        </form>
    </section>
</div>
@endsection

