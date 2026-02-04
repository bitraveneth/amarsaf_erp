@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit salary distribution</h1>
                <p>Update salary, allowances, payment method, or remarks.</p>
            </div>
            <a href="{{ route('admin.salary-distributions.index') }}" class="button-secondary">Back to distributions</a>
        </header>

        <form action="{{ route('admin.salary-distributions.update', $distribution) }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('admin.finance.salary_distributions.partials.form', ['distribution' => $distribution])
            <div class="form-actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection

