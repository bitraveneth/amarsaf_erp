@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit expense</h1>
                <p>Update amount, category, or status.</p>
            </div>
            <a href="{{ route('admin.expenses.index') }}" class="button-secondary">Back to expenses</a>
        </header>

        <form action="{{ route('admin.expenses.update', $expense) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            @include('admin.finance.expenses.partials.form', ['expense' => $expense])
            <div class="form-actions">
                <button type="submit">Update expense</button>
            </div>
        </form>
    </section>
</div>
@endsection

