@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add expense</h1>
                <p>Record a new expense entry.</p>
            </div>
            <a href="{{ route('admin.expenses.index') }}" class="button-secondary">Back to expenses</a>
        </header>

        <form action="{{ route('admin.expenses.store') }}" method="POST" class="form-form">
            @csrf
            @include('admin.finance.expenses.partials.form', ['expense' => $expense])
            <div class="form-actions">
                <button type="submit">Save expense</button>
            </div>
        </form>
    </section>
</div>
@endsection

