@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit allowance / TA slip for {{ $employee->name }}</h1>
                <p>Update dates, amounts, status, or slip.</p>
            </div>
            <a href="{{ route('admin.employees.allowances.index', $employee) }}" class="button-secondary">Back to allowances</a>
        </header>

        <form action="{{ route('admin.employees.allowances.update', [$employee, $allowance]) }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('admin.employees.allowances.partials.form', ['allowance' => $allowance])
            <div class="form-actions">
                <button type="submit">Update allowance</button>
            </div>
        </form>
    </section>
</div>
@endsection

