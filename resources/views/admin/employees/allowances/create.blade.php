@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add allowance / TA slip for {{ $employee->name }}</h1>
                <p>Submit a new allowance claim with optional slip.</p>
            </div>
            <a href="{{ route('admin.employees.allowances.index', $employee) }}" class="button-secondary">Back to allowances</a>
        </header>

        <form action="{{ route('admin.employees.allowances.store', $employee) }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @include('admin.employees.allowances.partials.form', ['allowance' => $allowance])
            <div class="form-actions">
                <button type="submit">Save allowance</button>
            </div>
        </form>
    </section>
</div>
@endsection

