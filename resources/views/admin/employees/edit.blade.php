@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit employee</h1>
                <p>Update employee profile details.</p>
            </div>
            <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
        </header>

        <form action="{{ route('admin.employees.update', $employee) }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('admin.employees.partials.form', ['employee' => $employee])
            <div class="form-actions">
                <button type="submit">Update employee</button>
                <a href="{{ route('admin.employees.badges.grant-form', $employee) }}" class="button-secondary">
                    Grant badge
                </a>
            </div>
        </form>
    </section>
</div>
@endsection
