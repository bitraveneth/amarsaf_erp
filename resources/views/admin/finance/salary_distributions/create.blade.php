@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add salary distribution</h1>
                <p>Record salary and allowances for one employee and period.</p>
            </div>
            <a href="{{ route('admin.salary-distributions.index') }}" class="button-secondary">Back to distributions</a>
        </header>

        <form action="{{ route('admin.salary-distributions.store') }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @include('admin.finance.salary_distributions.partials.form', ['distribution' => $distribution])
            <div class="form-actions">
                <button type="submit">Save distribution</button>
            </div>
        </form>
    </section>
</div>
@endsection

